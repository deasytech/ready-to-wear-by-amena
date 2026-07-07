<?php

use App\Models\Product;
use App\Models\User;

function adminUser(): User
{
    return User::factory()->create(['email' => 'super@admin.com']);
}

it('lets the admin view the dashboard with widgets', function () {
    $this->actingAs(adminUser())->get('/admin')->assertOk();
});

$resources = [
    'products', 'categories', 'colors', 'sizes', 'collections',
    'orders', 'payments', 'shipping-methods', 'discount-codes',
    'newsletter-subscribers', 'banners', 'blogs', 'stockists',
    'about-pages', 'company-addresses', 'users',
];

foreach ($resources as $resource) {
    it("lets the admin list the {$resource} resource", function () use ($resource) {
        $this->actingAs(adminUser())->get("/admin/{$resource}")->assertOk();
    });
}

it('lets the admin create and edit a product with variants', function () {
    $admin = adminUser();
    $product = Product::factory()->for(\App\Models\Category::factory())->create();

    $this->actingAs($admin)
        ->get("/admin/products/{$product->id}/edit")
        ->assertOk();

    $this->actingAs($admin)
        ->get('/admin/products/create')
        ->assertOk();
});

it('lets the admin view an order detail page', function () {
    $order = \App\Models\Order::factory()->create();

    $this->actingAs(adminUser())
        ->get("/admin/orders/{$order->id}")
        ->assertOk();
});
