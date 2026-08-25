<?php

use App\Livewire\Checkout\CheckoutFlow;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Livewire\Livewire;

it('places a cash-on-delivery order and decrements stock', function () {
    fakeShipBubble();

    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->for(Category::factory())->create(['price' => 50000]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    app(CartService::class)->addItem($product, $variant, 2);

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
        ->call('placeOrder')
        ->assertRedirect(route('checkout.success'));

    $order = Order::first();

    expect($order)->not->toBeNull();
    expect((float) $order->grand_total)->toBe(102500.0);
    expect($order->status)->toBe('confirmed');
    expect($order->items)->toHaveCount(1);

    expect($variant->fresh()->stock)->toBe(3);
});

it('prevents ordering more than available stock', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->for(Category::factory())->create(['price' => 50000]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 1]);

    $cartService = app(CartService::class);
    $cartService->addItem($product, $variant, 1);

    // Simulate a second checkout draining stock after the cart item was added.
    $variant->update(['stock' => 0]);

    Livewire::test(CheckoutFlow::class)
        ->set('first_name', 'Amaka')
        ->set('last_name', 'Okafor')
        ->set('email', 'amaka@example.com')
        ->set('phone', '+2348012345678')
        ->set('street_address', '10 Admiralty Way')
        ->set('city', 'Lekki')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->set('liveCouriers', [
            ['courier_name' => 'Fake Courier', 'display_amount' => 2500, 'display_currency' => 'NGN'],
        ])
        ->set('selectedCourierIndex', 0)
        ->set('payment_method', 'cod')
        ->set('step', 5)
        ->call('placeOrder')
        ->assertHasErrors('stock');

    expect(Order::count())->toBe(0);
});
