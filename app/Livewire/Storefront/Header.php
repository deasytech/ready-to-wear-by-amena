<?php

namespace App\Livewire\Storefront;

use App\Models\Collection;
use App\Services\CartService;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    public int $cartCount = 0;

    public int $wishlistCount = 0;

    public string $currentCurrency = 'NGN';

    public function mount(CartService $cartService, CurrencyService $currencyService): void
    {
        $this->refreshCartCount($cartService);
        $this->refreshWishlistCount();
        $this->currentCurrency = $currencyService->getCurrentCurrency();
    }

    /**
     * Switching currency reprices the active cart and needs every other
     * Livewire component on the page (product prices, cart totals, checkout)
     * to recompute against it, so a full page reload is the simplest way to
     * keep everything consistent rather than wiring events through every
     * storefront component individually.
     */
    public function changeCurrency(string $currency, CurrencyService $currencyService, CartService $cartService): void
    {
        if (! array_key_exists($currency, $currencyService->getSupportedCurrencies())) {
            return;
        }

        $currencyService->setCurrentCurrency($currency);
        $cartService->syncCurrency($cartService->current(), $currency);

        // Inside a Livewire action, url()->current() resolves to the AJAX
        // endpoint the browser just posted to (/livewire/update), not the page
        // the component is rendered on - redirecting there 404s/405s. The
        // Referer header is what the browser was actually looking at.
        $this->redirect(request()->header('Referer') ?? route('home'), navigate: false);
    }

    #[On('cart-updated')]
    public function refreshCartCount(CartService $cartService): void
    {
        $this->cartCount = (int) $cartService->current()->items()->sum('quantity');
    }

    #[On('wishlist-updated')]
    public function refreshWishlistCount(): void
    {
        $this->wishlistCount = Auth::check()
            ? (int) (Auth::user()->wishlist?->items()->count() ?? 0)
            : 0;
    }

    #[On('wishlist-toggle')]
    public function handleWishlistToggle(int $productId): void
    {
        if (! Auth::check()) {
            return;
        }

        $wishlist = Auth::user()->wishlist()->firstOrCreate([]);
        $item = $wishlist->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->delete();
        } else {
            $wishlist->items()->create(['product_id' => $productId]);
        }

        $this->refreshWishlistCount();
    }

    public function render(CurrencyService $currencyService)
    {
        $navCollections = Cache::remember(
            'nav-collections',
            now()->addMinutes(10),
            fn () => Collection::active()->orderBy('sort_order')->limit(3)->get()
        );

        return view('livewire.storefront.header', [
            'navCollections' => $navCollections,
            'currencies' => $currencyService->getSupportedCurrencies(),
        ]);
    }
}
