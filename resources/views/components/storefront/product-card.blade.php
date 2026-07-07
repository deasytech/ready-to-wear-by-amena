@props([
    'product',
    'wishlisted' => false,
    'eager' => false,
])

@php
    $currency = app(\App\Services\CurrencyService::class);
    $activeCurrency = $currency->getCurrentCurrency();
    $price = $product->getPriceForCurrency($activeCurrency);
    $isNew = $product->created_at?->gt(now()->subDays(21));
@endphp

<div
    class="group relative"
    x-data="{ wishlisted: @js($wishlisted), authenticated: @js(auth()->check()) }"
    x-init="if (! authenticated) { wishlisted = (JSON.parse(localStorage.getItem('rtw_wishlist') || '[]')).includes({{ $product->id }}) }"
>
    <a href="{{ route('products.show', $product) }}" wire:navigate class="block">
        <div class="relative aspect-[3/4] overflow-hidden bg-neutral-100">
            <img
                src="{{ $product->first_image }}"
                alt="{{ $product->name }}"
                loading="{{ $eager ? 'eager' : 'lazy' }}"
                class="absolute inset-0 size-full object-cover transition-opacity duration-500 {{ $product->second_image ? 'group-hover:opacity-0' : '' }}"
            >
            @if ($product->second_image)
                <img
                    src="{{ $product->second_image }}"
                    alt=""
                    aria-hidden="true"
                    loading="lazy"
                    class="absolute inset-0 size-full scale-105 object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100"
                >
            @endif

            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                @if ($isNew)
                    <span class="bg-black px-2.5 py-1 text-[10px] font-medium tracking-widest text-white uppercase">New</span>
                @endif
                @if ($product->on_sale)
                    <span class="bg-white px-2.5 py-1 text-[10px] font-medium tracking-widest text-black uppercase">Sale</span>
                @endif
            </div>
        </div>
    </a>

    <button
        type="button"
        class="rtw-focus absolute top-3 right-3 flex size-9 items-center justify-center bg-white/90 backdrop-blur-sm transition-colors hover:bg-white"
        :aria-pressed="wishlisted"
        aria-label="Add to wishlist"
        @click="
            wishlisted = !wishlisted;
            if (! authenticated) {
                let ids = JSON.parse(localStorage.getItem('rtw_wishlist') || '[]');
                ids = wishlisted ? [...new Set([...ids, {{ $product->id }}])] : ids.filter(id => id !== {{ $product->id }});
                localStorage.setItem('rtw_wishlist', JSON.stringify(ids));
            }
            $dispatch('wishlist-toggle', { productId: {{ $product->id }} });
        "
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" :fill="wishlisted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
    </button>

    <div class="mt-4 flex items-start justify-between gap-2">
        <div>
            <a href="{{ route('products.show', $product) }}" wire:navigate>
                <h3 class="text-sm text-black">{{ $product->name }}</h3>
            </a>
            <p class="mt-1 text-sm text-neutral-500">{{ $currency->formatForDisplay($price, $activeCurrency) }}</p>
        </div>
    </div>
</div>
