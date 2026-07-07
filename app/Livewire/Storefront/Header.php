<?php

namespace App\Livewire\Storefront;

use App\Models\Collection;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    public int $cartCount = 0;

    public int $wishlistCount = 0;

    public function mount(CartService $cartService): void
    {
        $this->refreshCartCount($cartService);
        $this->refreshWishlistCount();
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

    public function render()
    {
        $navCollections = Cache::remember(
            'nav-collections',
            now()->addMinutes(10),
            fn () => Collection::active()->orderBy('sort_order')->limit(3)->get()
        );

        return view('livewire.storefront.header', [
            'navCollections' => $navCollections,
        ]);
    }
}
