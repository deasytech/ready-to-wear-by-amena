<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\CartService;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\PaymentService;
use Tests\Fakes\FakePaystackGateway;

beforeEach(function () {
    $this->app->bind(PaymentGatewayInterface::class, FakePaystackGateway::class);
    FakePaystackGateway::$shouldSucceed = true;
});

it('redirects to the paystack authorization url when placing an online order', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->for(Category::factory())->create(['price' => 30000]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);
    $shippingMethod = ShippingMethod::factory()->create();

    app(CartService::class)->addItem($product, $variant, 1);

    $component = \Livewire\Livewire::test(\App\Livewire\Checkout\CheckoutFlow::class)
        ->set('first_name', 'Amaka')
        ->set('last_name', 'Okafor')
        ->set('email', 'amaka@example.com')
        ->set('phone', '+2348012345678')
        ->set('street_address', '10 Admiralty Way')
        ->set('city', 'Lekki')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->set('shipping_method_id', $shippingMethod->id)
        ->set('payment_method', 'paystack')
        ->set('step', 5)
        ->call('placeOrder');

    $order = Order::first();

    $component->assertRedirect('https://checkout.paystack.com/fake-'.$order->payment_reference);
    expect($order->payment_status)->toBe('pending');
    expect($order->payment_reference)->not->toBeNull();
});

it('marks the order paid when paystack verification succeeds via the callback', function () {
    $order = Order::factory()->create(['payment_status' => 'pending', 'status' => 'pending']);

    $paymentService = app(PaymentService::class);
    $payment = \App\Models\Payment::create([
        'order_id' => $order->id,
        'gateway' => 'paystack',
        'reference' => 'RTW-TEST123',
        'amount' => $order->grand_total,
        'currency' => 'NGN',
        'status' => 'pending',
    ]);

    $result = $paymentService->completePayment('RTW-TEST123');

    expect($result->status)->toBe('paid');
    expect($order->fresh()->payment_status)->toBe('paid');
    expect($order->fresh()->status)->toBe('confirmed');
});

it('marks the order failed when paystack verification fails', function () {
    FakePaystackGateway::$shouldSucceed = false;

    $order = Order::factory()->create(['payment_status' => 'pending', 'status' => 'pending']);

    \App\Models\Payment::create([
        'order_id' => $order->id,
        'gateway' => 'paystack',
        'reference' => 'RTW-TEST999',
        'amount' => $order->grand_total,
        'currency' => 'NGN',
        'status' => 'pending',
    ]);

    $result = app(PaymentService::class)->completePayment('RTW-TEST999');

    expect($result->status)->toBe('failed');
    expect($order->fresh()->payment_status)->toBe('failed');
});
