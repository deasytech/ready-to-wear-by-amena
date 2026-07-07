<div x-data @keydown.escape.window="$wire.open = false">
    <div
        x-show="$wire.open"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[60] bg-black/40"
        wire:click="close"
    ></div>

    <div
        x-show="$wire.open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-[60] flex w-full max-w-md flex-col bg-white shadow-xl"
    >
        <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-5">
            <h2 class="font-serif text-xl">Shopping Bag</h2>
            <button type="button" wire:click="close" class="rtw-focus flex size-9 items-center justify-center" aria-label="Close bag">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        @if ($cart->items->isEmpty())
            <div class="flex flex-1 items-center justify-center px-6">
                <x-storefront.empty-state
                    title="Your bag is empty"
                    actionLabel="Continue Shopping"
                    :actionHref="route('shop.index')"
                />
            </div>
        @else
            <div class="flex-1 divide-y divide-neutral-100 overflow-y-auto px-6">
                @foreach ($cart->items as $item)
                    <div class="flex gap-4 py-5">
                        <div class="size-20 shrink-0 bg-neutral-100">
                            <img src="{{ $item->product->first_image }}" alt="{{ $item->product->name }}" class="size-full object-cover">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-medium">{{ $item->product->name }}</p>
                                <button type="button" wire:click="removeItem({{ $item->id }})" class="text-neutral-400 hover:text-black" aria-label="Remove item">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            @if ($item->variant)
                                <p class="mt-0.5 text-xs text-neutral-500">
                                    {{ collect([$item->variant->color?->name, $item->variant->size?->name])->filter()->implode(' / ') }}
                                </p>
                            @endif
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-center border border-neutral-300">
                                    <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ max(1, $item->quantity - 1) }})" class="flex size-7 items-center justify-center" aria-label="Decrease quantity">&minus;</button>
                                    <span class="w-6 text-center text-xs">{{ $item->quantity }}</span>
                                    <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="flex size-7 items-center justify-center" aria-label="Increase quantity">+</button>
                                </div>
                                <p class="text-sm">{{ app(\App\Services\CurrencyService::class)->formatForDisplay($item->unit_price * $item->quantity, $activeCurrency) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-neutral-200 px-6 py-6">
                <div class="mb-4 flex items-center justify-between text-sm">
                    <span class="text-neutral-500">Subtotal</span>
                    <span class="font-medium">{{ app(\App\Services\CurrencyService::class)->formatForDisplay($cart->subtotal, $activeCurrency) }}</span>
                </div>
                <a href="{{ route('checkout.index') }}" class="rtw-btn-primary block w-full text-center">Checkout</a>
                <a href="{{ route('cart.index') }}" wire:navigate class="rtw-link-underline mt-4 block text-center text-xs tracking-wide uppercase">View Full Bag</a>
            </div>
        @endif
    </div>
</div>
