<?php

use App\Livewire\Shop\Show;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('adds a simple product to the cart from the product page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->for(Category::factory())->create(['in_stock' => true]);

    Livewire::test(Show::class, ['product' => $product])
        ->call('addToBag')
        ->assertHasNoErrors();

    expect((int) $user->fresh()->cart->items()->sum('quantity'))->toBe(1);
});

it('renders the wishlist page for guests and authenticated users', function () {
    $this->get(route('wishlist.index'))->assertOk();

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('wishlist.index'))->assertOk();
});

it('toggles a product in and out of the authenticated wishlist', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->for(Category::factory())->create();

    $component = Livewire::test(Show::class, ['product' => $product]);

    $component->call('toggleWishlist');
    expect($user->fresh()->wishlist->items()->where('product_id', $product->id)->exists())->toBeTrue();

    $component->call('toggleWishlist');
    expect($user->fresh()->wishlist->items()->where('product_id', $product->id)->exists())->toBeFalse();
});
