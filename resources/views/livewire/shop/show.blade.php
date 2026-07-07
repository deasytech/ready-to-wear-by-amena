@php
    $currency = app(\App\Services\CurrencyService::class);
@endphp

<div>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => strip_tags($product->description ?? ''),
            'image' => $product->image_urls,
            'sku' => (string) $product->id,
            'category' => $product->category?->name,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('products.show', $product),
                'priceCurrency' => $activeCurrency,
                'price' => number_format($price, 2, '.', ''),
                'availability' => $isAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <div class="rtw-container py-6 text-xs tracking-wide text-neutral-500 uppercase">
        <a href="{{ route('shop.index') }}" wire:navigate class="rtw-link-underline">Shop</a>
        <span class="mx-2">/</span>
        @if ($product->category)
            <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" wire:navigate class="rtw-link-underline">{{ $product->category->name }}</a>
            <span class="mx-2">/</span>
        @endif
        <span class="text-black">{{ $product->name }}</span>
    </div>

    <div class="rtw-container grid gap-10 pb-16 lg:grid-cols-2 lg:gap-16 lg:pb-24">
        {{-- Gallery --}}
        <div>
            <div class="flex snap-x snap-mandatory gap-2 overflow-x-auto lg:grid lg:snap-none lg:grid-cols-1 lg:gap-4 lg:overflow-visible">
                @forelse ($product->image_urls as $image)
                    <div class="aspect-[3/4] w-[85%] shrink-0 snap-start bg-neutral-100 lg:w-full">
                        <img src="{{ $image }}" alt="{{ $product->name }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" class="size-full object-cover">
                    </div>
                @empty
                    <div class="aspect-[3/4] w-full bg-neutral-100"></div>
                @endforelse
            </div>
        </div>

        {{-- Details --}}
        <div class="lg:max-w-md">
            <h1 class="font-serif text-3xl">{{ $product->name }}</h1>
            <p class="mt-3 text-lg text-neutral-700">{{ $currency->formatForDisplay($price, $activeCurrency) }}</p>

            @error('stock') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror

            @if ($product->colors->isNotEmpty())
                <div class="mt-8">
                    <h2 class="rtw-label mb-3">Color</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($product->colors as $color)
                            <button
                                type="button"
                                wire:click="$set('selectedColorId', {{ $color->id }})"
                                title="{{ $color->name }}"
                                class="size-9 rounded-full border-2 {{ $selectedColorId === $color->id ? 'border-black' : 'border-transparent' }}"
                                style="background-color: {{ $color->hex_code }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.15);"
                            >
                                <span class="sr-only">{{ $color->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($product->sizes->isNotEmpty())
                <div class="mt-8">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="rtw-label">Size</h2>
                        <button type="button" wire:click="$set('activeAccordion', 'size-guide')" class="rtw-link-underline text-xs tracking-wide uppercase">Size Guide</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($product->sizes as $size)
                            <button
                                type="button"
                                wire:click="$set('selectedSizeId', {{ $size->id }})"
                                class="flex h-11 min-w-11 items-center justify-center border px-3 text-sm {{ $selectedSizeId === $size->id ? 'border-black bg-black text-white' : 'border-neutral-300 hover:border-black' }}"
                            >
                                {{ $size->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-8">
                <h2 class="rtw-label mb-3">Quantity</h2>
                <div class="flex w-32 items-center border border-neutral-300">
                    <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" class="flex flex-1 items-center justify-center py-2.5" aria-label="Decrease quantity">&minus;</button>
                    <span class="w-10 text-center text-sm">{{ $quantity }}</span>
                    <button type="button" wire:click="$set('quantity', {{ $quantity + 1 }})" class="flex flex-1 items-center justify-center py-2.5" aria-label="Increase quantity">+</button>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" wire:click="addToBag" wire:loading.attr="disabled" class="rtw-btn-primary flex-1 disabled:opacity-50">
                    <span wire:loading.remove wire:target="addToBag">{{ $isAvailable ? 'Add to Bag' : 'Out of Stock' }}</span>
                    <span wire:loading wire:target="addToBag">Adding&hellip;</span>
                </button>
                <button type="button" wire:click="toggleWishlist" class="rtw-focus flex size-12 shrink-0 items-center justify-center border border-black" aria-label="Toggle wishlist" :aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </button>
            </div>

            {{-- Accordions --}}
            <div class="mt-12 divide-y divide-neutral-200 border-t border-b border-neutral-200">
                @foreach ([
                    'details' => 'Product Details',
                    'fabric' => 'Fabric & Care',
                    'delivery' => 'Delivery & Returns',
                    'size-guide' => 'Size Guide',
                ] as $key => $label)
                    <div>
                        <button type="button" wire:click="$set('activeAccordion', '{{ $activeAccordion === $key ? '' : $key }}')" class="flex w-full items-center justify-between py-4 text-left text-sm font-medium tracking-wide uppercase">
                            {{ $label }}
                            <span class="text-lg leading-none">{{ $activeAccordion === $key ? '−' : '+' }}</span>
                        </button>
                        @if ($activeAccordion === $key)
                            <div class="pb-4 text-sm leading-relaxed text-neutral-600">
                                @if ($key === 'details')
                                    {!! $product->description !!}
                                @elseif ($key === 'fabric')
                                    <p>Dry clean or hand wash cold. Do not tumble dry. Store on a padded hanger away from direct sunlight. Refer to the care label for fabric-specific instructions.</p>
                                @elseif ($key === 'delivery')
                                    <p>Standard delivery within Nigeria takes 3&ndash;7 business days; express takes 1&ndash;2 business days. Unworn items with tags may be returned within 7 days. See our <a href="{{ route('pages.shipping-returns') }}" wire:navigate class="rtw-link-underline">Shipping &amp; Returns</a> page for full details.</p>
                                @else
                                    <p>Sizes run true to standard international sizing. If you're between sizes for structured tailoring, we recommend sizing up.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Related products --}}
    @if ($relatedProducts->isNotEmpty())
        <section class="rtw-container border-t border-neutral-200 py-16 lg:py-24">
            <h2 class="mb-10 font-serif text-3xl">You May Also Like</h2>
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-x-8">
                @foreach ($relatedProducts as $related)
                    <x-storefront.product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif
</div>
