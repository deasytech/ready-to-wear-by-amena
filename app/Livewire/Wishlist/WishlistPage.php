<?php

namespace App\Livewire\Wishlist;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
#[Title('Wishlist')]
class WishlistPage extends Component
{
    public array $guestProductIds = [];

    public bool $guestIdsLoaded = false;

    public function loadGuestIds(array $ids): void
    {
        $this->guestProductIds = array_map('intval', $ids);
        $this->guestIdsLoaded = true;
    }

    public function removeGuestProduct(int $productId): void
    {
        $this->guestProductIds = array_values(array_diff($this->guestProductIds, [$productId]));
        $this->dispatch('guest-wishlist-remove', productId: $productId);
    }

    public function removeItem(int $wishlistItemId): void
    {
        $item = Auth::user()->wishlist?->items()->whereKey($wishlistItemId)->first();
        $item?->delete();
    }

    public function moveToBag(int $productId, CartService $cartService): void
    {
        $product = Product::find($productId);

        if ($product) {
            $cartService->addItem($product, null, 1);
            $this->dispatch('cart-updated');
        }

        if (Auth::check()) {
            $item = Auth::user()->wishlist?->items()->where('product_id', $productId)->first();
            $item?->delete();
        } else {
            $this->removeGuestProduct($productId);
        }
    }

    public function render()
    {
        if (Auth::check()) {
            $rows = (Auth::user()->wishlist?->items()->with('product.category')->latest()->get() ?? collect())
                ->filter(fn ($item) => $item->product !== null)
                ->map(fn ($item) => ['product' => $item->product, 'wishlistItemId' => $item->id]);
        } else {
            $rows = $this->guestIdsLoaded
                ? Product::query()->whereIn('id', $this->guestProductIds)->get()
                    ->map(fn ($product) => ['product' => $product, 'wishlistItemId' => null])
                : collect();
        }

        return view('livewire.wishlist.wishlist-page', [
            'rows' => $rows,
        ]);
    }
}
