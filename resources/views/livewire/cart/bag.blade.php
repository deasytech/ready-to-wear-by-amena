@php $currency = app(\App\Services\CurrencyService::class); @endphp

<div class="rtw-container py-16">
    <p class="rtw-label mb-4">Your Bag</p>
    <h1 class="font-serif text-3xl lg:text-4xl">Shopping Bag</h1>

    @if ($cart->items->isEmpty())
        <x-storefront.empty-state
            title="Your bag is empty"
            description="Explore the collection and find something you love."
            actionLabel="Continue Shopping"
            :actionHref="route('shop.index')"
        />
    @else
        <div class="mt-10 grid gap-12 lg:grid-cols-3">
            <div class="divide-y divide-neutral-200 border-t border-b border-neutral-200 lg:col-span-2">
                @foreach ($cart->items as $item)
                    <div class="flex gap-5 py-6">
                        <a href="{{ route('products.show', $item->product) }}" wire:navigate class="size-28 shrink-0 bg-neutral-100">
                            <img src="{{ $item->product->first_image }}" alt="{{ $item->product->name }}" class="size-full object-cover">
                        </a>
                        <div class="flex flex-1 flex-col justify-between">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <a href="{{ route('products.show', $item->product) }}" wire:navigate class="text-sm font-medium">{{ $item->product->name }}</a>
                                    @if ($item->variant)
                                        <p class="mt-1 text-xs text-neutral-500">
                                            {{ collect([$item->variant->color?->name, $item->variant->size?->name])->filter()->implode(' / ') }}
                                        </p>
                                    @endif
                                </div>
                                <p class="text-sm">{{ $currency->formatForDisplay($item->unit_price * $item->quantity, $activeCurrency) }}</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center border border-neutral-300">
                                    <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ max(1, $item->quantity - 1) }})" class="flex size-8 items-center justify-center" aria-label="Decrease quantity">&minus;</button>
                                    <span class="w-8 text-center text-sm">{{ $item->quantity }}</span>
                                    <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="flex size-8 items-center justify-center" aria-label="Increase quantity">+</button>
                                </div>
                                <button type="button" wire:click="removeItem({{ $item->id }})" class="rtw-link-underline text-xs tracking-wide text-neutral-500 uppercase">Remove</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>
                <h2 class="rtw-label mb-4">Order Summary</h2>
                <div class="space-y-3 border border-neutral-200 p-6 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Subtotal</span>
                        <span>{{ $currency->formatForDisplay($cart->subtotal, $activeCurrency) }}</span>
                    </div>
                    <p class="text-xs text-neutral-500">Shipping and any discounts are calculated at checkout.</p>
                </div>
                <a href="{{ route('checkout.index') }}" class="rtw-btn-primary mt-6 block w-full text-center">Proceed to Checkout</a>
                <a href="{{ route('shop.index') }}" wire:navigate class="rtw-link-underline mt-4 block text-center text-xs tracking-wide uppercase">Continue Shopping</a>
            </div>
        </div>
    @endif
</div>
