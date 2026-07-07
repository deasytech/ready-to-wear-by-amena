<?php

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;

it('renders the homepage', function () {
    Product::factory()->for(Category::factory())->create();

    $this->get(route('home'))->assertOk()->assertSee('Ready To Wear by Amena');
});

it('renders the shop page and filters by category', function () {
    $category = Category::factory()->create(['name' => 'Dresses']);
    Product::factory()->for($category)->create(['name' => 'The Amara Dress']);

    $this->get(route('shop.index'))->assertOk()->assertSee('The Amara Dress');

    $this->get(route('shop.index', ['category' => $category->slug]))
        ->assertOk()
        ->assertSee('The Amara Dress');
});

it('renders a product detail page', function () {
    $product = Product::factory()->for(Category::factory())->create();

    $this->get(route('products.show', $product))->assertOk()->assertSee($product->name);
});

it('renders a collection page', function () {
    $collection = Collection::factory()->create();
    $product = Product::factory()->for(Category::factory())->create();
    $collection->products()->attach($product);

    $this->get(route('collections.show', $collection))->assertOk()->assertSee($collection->name);
});

it('renders the standalone about page with its own content', function () {
    \App\Models\About::factory()->create([
        'section_name' => 'main_about',
        'title' => 'About Us',
        'content' => 'A womenswear label built on precise tailoring.',
        'is_active' => true,
    ]);

    $this->get(route('pages.about'))
        ->assertOk()
        ->assertSee('About Us')
        ->assertSee('A womenswear label built on precise tailoring.');

    $this->get(route('home'))->assertDontSee('id="main-about"', false);
});

it('returns search results', function () {
    Product::factory()->for(Category::factory())->create(['name' => 'The Amara Dress']);

    $this->get(route('search.index', ['q' => 'Amara']))->assertOk()->assertSee('The Amara Dress');
});

it('redirects guests away from the account area', function () {
    $this->get(route('account.overview'))->assertRedirect(route('login'));
});

it('lets authenticated customers view their account overview', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.overview'))
        ->assertOk()
        ->assertSee('Welcome back');
});

it('lets authenticated customers view order history and addresses', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('account.orders'))->assertOk();
    $this->actingAs($user)->get(route('account.addresses'))->assertOk();
});

it('renders the auth pages with rtw branding', function () {
    $this->get(route('login'))->assertOk()->assertSee('Ready To Wear by Amena');
    $this->get(route('register'))->assertOk();
});

it('renders account settings pages in the storefront layout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('settings.profile'))->assertOk()->assertSee('Ready To Wear by Amena');
    $this->actingAs($user)->get(route('settings.password'))->assertOk();
});
