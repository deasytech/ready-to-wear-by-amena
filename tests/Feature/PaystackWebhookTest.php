<?php

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;
use Tests\Fakes\FakePaystackGateway;

beforeEach(function () {
    FakePaystackGateway::$shouldSucceed = true;
});

it('rejects a webhook with an invalid signature', function () {
    config(['services.paystack.secret_key' => 'test-secret']);

    $this->postJson(route('paystack.webhook'), ['event' => 'charge.success'], [
        'x-paystack-signature' => 'invalid',
    ])->assertStatus(401);
});

it('confirms payment on a valid webhook signature', function () {
    $this->app->bind(PaymentGatewayInterface::class, FakePaystackGateway::class);

    config(['services.paystack.secret_key' => 'test-secret']);

    $order = Order::factory()->create(['payment_status' => 'pending', 'status' => 'pending']);
    Payment::create([
        'order_id' => $order->id,
        'gateway' => 'paystack',
        'reference' => 'RTW-WEBHOOK1',
        'amount' => $order->grand_total,
        'currency' => 'NGN',
        'status' => 'pending',
    ]);

    $payload = json_encode(['event' => 'charge.success', 'data' => ['reference' => 'RTW-WEBHOOK1']]);
    $signature = hash_hmac('sha512', $payload, 'test-secret');

    $this->call('POST', route('paystack.webhook'), [], [], [], [
        'HTTP_x-paystack-signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertStatus(204);

    expect($order->fresh()->payment_status)->toBe('paid');
});
