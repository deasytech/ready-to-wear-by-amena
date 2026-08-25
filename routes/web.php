<?php

use App\Http\Controllers\PaystackController;
use App\Http\Controllers\ShipBubbleWebhookController;
use App\Livewire\Cart\Bag;
use App\Livewire\Checkout\Cancel;
use App\Livewire\Checkout\CheckoutFlow;
use App\Livewire\Checkout\Success;
use App\Livewire\Collections\Show as CollectionShow;
use App\Livewire\Home\HomePage;
use App\Livewire\Search\SearchPage;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Shop\Index as ShopIndex;
use App\Livewire\Shop\Show as ProductShow;
use App\Livewire\UserDashboard\Addresses;
use App\Livewire\UserDashboard\OrderShow;
use App\Livewire\UserDashboard\Orders;
use App\Livewire\UserDashboard\Overview;
use App\Livewire\Wishlist\WishlistPage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');

Route::get('/shop', ShopIndex::class)->name('shop.index');
Route::get('/collections/{collection:slug}', CollectionShow::class)->name('collections.show');
Route::get('/products/{product:slug}', ProductShow::class)->name('products.show');
Route::get('/search', SearchPage::class)->name('search.index');

Route::get('/bag', Bag::class)->name('cart.index');

Route::get('/checkout', CheckoutFlow::class)->name('checkout.index');
Route::get('/checkout/success', Success::class)->name('checkout.success');
Route::get('/checkout/cancel', Cancel::class)->name('checkout.cancel');

Route::get('/wishlist', WishlistPage::class)->name('wishlist.index');

Route::get('/paystack/callback', [PaystackController::class, 'handleCallback'])->name('paystack.callback');
Route::post('/paystack/webhook', [PaystackController::class, 'handleWebhook'])->name('paystack.webhook');

Route::post('/shipbubble/webhook', [ShipBubbleWebhookController::class, 'handle'])->name('shipbubble.webhook');

Route::view('/about', 'pages.about')->name('pages.about');
Route::view('/contact', 'pages.contact')->name('pages.contact');
Route::view('/shipping-returns', 'pages.shipping-returns')->name('pages.shipping-returns');
Route::view('/privacy-policy', 'pages.privacy-policy')->name('pages.privacy-policy');
Route::view('/terms-conditions', 'pages.terms-conditions')->name('pages.terms-conditions');

Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', Overview::class)->name('index');
    Route::get('/orders', Orders::class)->name('orders');
    Route::get('/orders/{order}', OrderShow::class)->name('orders.show');
    Route::get('/addresses', Addresses::class)->name('addresses');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('generate', function () {
    Artisan::call('storage:link');
    echo 'storage generated';
});

Route::get('optimize', function () {
    Artisan::call('optimize:clear');
    echo 'site optimized';
});

Route::get('migrate', function () {
    Artisan::call('migrate');
    echo 'database migrated';
});

require __DIR__ . '/auth.php';
