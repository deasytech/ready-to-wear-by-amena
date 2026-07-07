<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order from a cart inside a transaction, locking variant rows
     * so concurrent checkouts can never oversell stock.
     *
     * @throws \RuntimeException when any line item no longer has enough stock
     */
    public function createFromCart(
        Cart $cart,
        array $addressData,
        ShippingMethod $shippingMethod,
        ?DiscountCode $discountCode,
        string $paymentMethod,
        array $orderMeta = []
    ): Order {
        return DB::transaction(function () use ($cart, $addressData, $shippingMethod, $discountCode, $paymentMethod, $orderMeta) {
            $cart->load('items.product', 'items.variant');

            if ($cart->items->isEmpty()) {
                throw new \RuntimeException('Your bag is empty.');
            }

            // Lock the variant rows involved so a concurrent checkout can't oversell.
            $variantIds = $cart->items->pluck('product_variant_id')->filter()->all();
            $lockedVariants = ProductVariant::whereIn('id', $variantIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($cart->items as $item) {
                if ($item->product_variant_id) {
                    $variant = $lockedVariants->get($item->product_variant_id);

                    if (! $variant || $variant->stock < $item->quantity) {
                        throw new \RuntimeException("\"{$item->product->name}\" no longer has enough stock.");
                    }
                } elseif (! $item->product->in_stock) {
                    throw new \RuntimeException("\"{$item->product->name}\" is no longer in stock.");
                }
            }

            $subtotal = (float) $cart->items->sum(fn ($item) => $item->unit_price * $item->quantity);
            $discountAmount = 0;

            if ($discountCode && $discountCode->isValidFor($subtotal)) {
                $discountAmount = $discountCode->calculateDiscount($subtotal);
            }

            $grandTotal = round($subtotal - $discountAmount + $shippingMethod->cost, 2);

            $order = Order::create([
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'currency' => $cart->currency,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'status' => 'pending',
                'shipping_amount' => $shippingMethod->cost,
                'shipping_method' => $shippingMethod->code,
                'shipping_method_id' => $shippingMethod->id,
                'discount_code_id' => $discountCode?->id,
                'notes' => $orderMeta['notes'] ?? null,
            ]);

            $order->addresses()->create([
                ...$addressData,
                'user_id' => auth()->id(),
                'address_type' => 'shipping',
            ]);

            foreach ($cart->items as $item) {
                $variant = $item->product_variant_id ? $lockedVariants->get($item->product_variant_id) : null;

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $item->product->name,
                    'color' => $variant?->color?->name,
                    'size' => $variant?->size?->name,
                    'sku' => $variant?->sku,
                    'quantity' => $item->quantity,
                    'unit_amount' => $item->unit_price,
                    'total_amount' => $item->unit_price * $item->quantity,
                ]);

                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                }
            }

            if ($discountCode) {
                $discountCode->increment('used_count');
                $discountCode->usages()->create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                ]);
            }

            $cart->items()->delete();

            return $order->fresh(['items', 'address']);
        });
    }
}
