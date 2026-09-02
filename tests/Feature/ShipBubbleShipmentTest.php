<?php

use App\Livewire\Checkout\CheckoutFlow;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\ShipBubbleService;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Fakes\FakePaystackGateway;

it('books a shipment automatically when a COD order is placed', function () {
    fakeShipBubble();

    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['price' => 50000]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    app(CartService::class)->addItem($product, $variant, 1);

    Livewire::test(CheckoutFlow::class)
        ->set('first_name', 'Amaka')
        ->set('last_name', 'Okafor')
        ->set('email', 'amaka@example.com')
        ->set('phone', '+2348012345678')
        ->call('nextStep')
        ->set('street_address', '10 Admiralty Way')
        ->set('city', 'Lekki')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->call('nextStep')
        ->call('nextStep')
        ->set('payment_method', 'cod')
        ->call('nextStep')
        ->call('placeOrder');

    $order = Order::first();

    expect($order->shipbubble_request_token)->toBe('fake-request-token');
    expect($order->shipment_id)->toBe('SB-FAKE12345');
    expect($order->tracking_url)->toBe('https://shipbubble.test/tracking/SB-FAKE12345');
});

it('books a shipment once payment is confirmed via the paystack callback', function () {
    fakeShipBubble();

    $this->app->bind(PaymentGatewayInterface::class, FakePaystackGateway::class);
    FakePaystackGateway::$shouldSucceed = true;

    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['price' => 50000]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    app(CartService::class)->addItem($product, $variant, 1);

    Livewire::test(CheckoutFlow::class)
        ->set('first_name', 'Amaka')
        ->set('last_name', 'Okafor')
        ->set('email', 'amaka@example.com')
        ->set('phone', '+2348012345678')
        ->call('nextStep')
        ->set('street_address', '10 Admiralty Way')
        ->set('city', 'Lekki')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->call('nextStep')
        ->call('nextStep')
        ->set('payment_method', 'paystack')
        ->call('nextStep')
        ->call('placeOrder');

    $order = Order::first();
    expect($order->shipment_id)->toBeNull();

    $this->get(route('paystack.callback', ['reference' => $order->payment_reference]))
        ->assertRedirect(route('checkout.success'));

    expect($order->fresh()->shipment_id)->toBe('SB-FAKE12345');
});

it('never books a shipment twice', function () {
    Http::fake([
        '*/shipping/labels' => Http::sequence()
            ->push(['status' => 'success', 'data' => ['order_id' => 'SB-FIRST', 'tracking_url' => 'https://x.test/1']], 200)
            ->push(['status' => 'success', 'data' => ['order_id' => 'SB-SECOND', 'tracking_url' => 'https://x.test/2']], 200),
    ]);

    $order = Order::factory()->create([
        'shipbubble_request_token' => 'token',
        'shipbubble_service_code' => 'code',
        'shipbubble_courier_id' => 'courier',
    ]);

    $orderService = app(OrderService::class);
    $shipBubble = app(ShipBubbleService::class);

    $orderService->bookShipment($order, $shipBubble);
    $orderService->bookShipment($order->fresh(), $shipBubble);

    expect($order->fresh()->shipment_id)->toBe('SB-FIRST');
    Http::assertSentCount(1);
});

it('verifies the shipbubble webhook signature and updates order status', function () {
    $order = Order::factory()->create(['shipment_id' => 'SB-TRACK-1', 'status' => 'confirmed']);

    $secret = config('services.shipbubble.api_key');
    $payload = json_encode(['order_id' => 'SB-TRACK-1', 'status' => 'in_transit']);
    $signature = hash_hmac('sha512', $payload, $secret);

    $this->call('POST', route('shipbubble.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_SHIP_SIGNATURE' => $signature,
    ], $payload)->assertNoContent();

    expect($order->fresh()->status)->toBe('shipped');
});

it('rejects a shipbubble webhook with an invalid signature', function () {
    $order = Order::factory()->create(['shipment_id' => 'SB-TRACK-2', 'status' => 'confirmed']);

    $payload = json_encode(['order_id' => 'SB-TRACK-2', 'status' => 'completed']);

    $this->call('POST', route('shipbubble.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_SHIP_SIGNATURE' => 'not-the-real-signature',
    ], $payload)->assertStatus(401);

    expect($order->fresh()->status)->toBe('confirmed');
});
