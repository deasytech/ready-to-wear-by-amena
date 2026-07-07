<?php

namespace App\Livewire\Cart;

use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CurrencyService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
#[Title('Shopping Bag')]
class Bag extends Component
{
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

    public function render(CartService $cartService, CurrencyService $currencyService)
    {
        $cart = $cartService->current()->load('items.product', 'items.variant.color', 'items.variant.size');

        return view('livewire.cart.bag', [
            'cart' => $cart,
            'activeCurrency' => $currencyService->getCurrentCurrency(),
        ]);
    }
}
