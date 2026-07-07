<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    protected const COOKIE_NAME = 'cart_token';

    public function __construct(protected CurrencyService $currency) {}

    public function current(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                ['currency' => $this->currency->getCurrentCurrency()]
            );
        }

        $token = Cookie::get(self::COOKIE_NAME);

        if ($token) {
            $cart = Cart::where('session_token', $token)->first();

            if ($cart) {
                return $cart;
            }
        }

        $token = (string) Str::uuid();
        Cookie::queue(self::COOKIE_NAME, $token, 60 * 24 * 60);

        return Cart::create([
            'session_token' => $token,
            'currency' => $this->currency->getCurrentCurrency(),
        ]);
    }

    public function addItem(Product $product, ?ProductVariant $variant, int $quantity = 1): CartItem
    {
        $cart = $this->current();

        $availableStock = $variant ? $variant->stock : ($product->in_stock ? PHP_INT_MAX : 0);

        $existing = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        $desiredQuantity = ($existing?->quantity ?? 0) + $quantity;

        if ($desiredQuantity > $availableStock) {
            throw new \RuntimeException('Not enough stock available for this selection.');
        }

        $unitPrice = $variant?->price_override ?? $product->getPriceForCurrency($cart->currency);

        if ($existing) {
            $existing->update(['quantity' => $desiredQuantity, 'unit_price' => $unitPrice]);

            return $existing;
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        $availableStock = $item->variant ? $item->variant->stock : ($item->product->in_stock ? PHP_INT_MAX : 0);

        $quantity = max(1, min($quantity, $availableStock));

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function mergeGuestCartIntoUser(User $user): void
    {
        $token = Cookie::get(self::COOKIE_NAME);

        if (! $token) {
            return;
        }

        $guestCart = Cart::where('session_token', $token)->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id], ['currency' => $guestCart->currency]);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
            } else {
                $userCart->items()->create($guestItem->only(['product_id', 'product_variant_id', 'quantity', 'unit_price']));
            }
        }

        $guestCart->delete();
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
}
