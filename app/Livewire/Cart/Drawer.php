<?php

namespace App\Livewire\Cart;

use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CurrencyService;
use Livewire\Attributes\On;
use Livewire\Component;

class Drawer extends Component
{
    public bool $open = false;

    #[On('open-cart-drawer')]
    public function openDrawer(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function updateQuantity(int $itemId, int $quantity, CartService $cartService): void
    {
        $item = CartItem::find($itemId);

        if ($item && $item->cart_id === $cartService->current()->id) {
            $cartService->updateQuantity($item, $quantity);
        }

        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId, CartService $cartService): void
    {
        $item = CartItem::find($itemId);

        if ($item && $item->cart_id === $cartService->current()->id) {
            $cartService->removeItem($item);
        }

        $this->dispatch('cart-updated');
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        // Re-render is triggered automatically; method exists to register the listener.
    }

    public function render(CartService $cartService, CurrencyService $currencyService)
    {
        $cart = $cartService->current()->load('items.product', 'items.variant.color', 'items.variant.size');

        return view('livewire.cart.drawer', [
            'cart' => $cart,
            'activeCurrency' => $currencyService->getCurrentCurrency(),
        ]);
    }
}
