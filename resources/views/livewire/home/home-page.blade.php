@php
    $currency = app(\App\Services\CurrencyService::class);
    $activeCurrency = $currency->getCurrentCurrency();
    $about = \App\Models\About::active()->where('section_name', 'main_about')->first();
@endphp

<div>
    {{-- Hero --}}
    <section class="relative flex h-[85vh] min-h-[560px] items-end overflow-hidden bg-neutral-900 text-white">
        @if ($heroBanner)
            <img src="{{ $heroBanner->image_url }}" alt="{{ $heroBanner->title }}"
                class="absolute inset-0 size-full object-cover" fetchpriority="high">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
        <div class="rtw-container relative pb-16 lg:pb-24">
            <p class="rtw-label mb-4 text-white/80">New Season</p>
            <h1 class="max-w-2xl font-serif text-4xl leading-[1.1] sm:text-5xl lg:text-6xl">
                {{ $heroBanner?->title ?? 'Ready-To-Wear by Amena' }}
            </h1>
            <p class="mt-5 max-w-md text-sm text-white/80 lg:text-base">
                {{ $heroBanner?->description ?? 'Precise tailoring and considered fabrics, designed to anchor a wardrobe.' }}
            </p>
            <a href="{{ route('shop.index') }}" wire:navigate class="rtw-btn-ghost mt-8 inline-flex">Shop Collection</a>
        </div>
    </section>

    {{-- New Arrivals --}}
    @if ($newArrivals->isNotEmpty())
        <section class="rtw-container py-16 lg:py-24">
            <div class="mb-10 flex items-end justify-between">
                <div>
                    <p class="rtw-label mb-3">Just In</p>
                    <h2 class="font-serif text-3xl lg:text-4xl">New Arrivals</h2>
                </div>
                <a href="{{ route('shop.index', ['sort' => 'newest']) }}" wire:navigate
                    class="rtw-link-underline hidden text-xs font-medium tracking-widest uppercase sm:inline-block">View
                    All</a>
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-x-8">
                @foreach ($newArrivals as $product)
                    <x-storefront.product-card :product="$product" :eager="$loop->index < 2" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured Collection --}}
    @if ($featuredCollection)
        <section class="grid lg:grid-cols-2">
            <div class="relative aspect-[4/3] lg:aspect-auto">
                <img src="{{ $featuredCollection->image_url }}" alt="{{ $featuredCollection->name }}"
                    class="absolute inset-0 size-full object-cover" loading="lazy">
            </div>
            <div class="flex flex-col items-start justify-center bg-neutral-50 px-8 py-16 lg:px-16">
                <p class="rtw-label mb-4">Featured Collection</p>
                <h2 class="font-serif text-3xl lg:text-4xl">{{ $featuredCollection->name }}</h2>
                <p class="mt-5 max-w-md text-sm text-neutral-600">{{ $featuredCollection->description }}</p>
                <a href="{{ route('collections.show', $featuredCollection) }}" wire:navigate
                    class="rtw-btn-primary mt-8">Discover the Collection</a>
            </div>
        </section>
    @endif

    {{-- Shop by Category --}}
    @if ($categories->isNotEmpty())
        <section class="rtw-container py-16 lg:py-24">
            <div class="mb-10">
                <p class="rtw-label mb-3">Explore</p>
                <h2 class="font-serif text-3xl lg:text-4xl">Shop by Category</h2>
            </div>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                @foreach ($categories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}" wire:navigate
                        class="group relative block aspect-[3/4] overflow-hidden bg-neutral-100">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy"
                            class="absolute inset-0 size-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black/10 transition-colors group-hover:bg-black/25"></div>
                        <span
                            class="absolute bottom-4 left-4 text-sm font-medium tracking-widest text-white uppercase">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Campaign Banner --}}
    @if ($campaignBanner)
        <section
            class="relative flex h-[60vh] min-h-[420px] items-center justify-center overflow-hidden bg-neutral-900 text-center text-white">
            <img src="{{ $campaignBanner->image_url }}" alt="{{ $campaignBanner->title }}"
                class="absolute inset-0 size-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative px-6">
                <h2 class="font-serif text-3xl lg:text-5xl">{{ $campaignBanner->title }}</h2>
                <p class="mx-auto mt-4 max-w-lg text-sm text-white/80 lg:text-base">{{ $campaignBanner->description }}
                </p>
                <a href="{{ route('shop.index') }}" wire:navigate class="rtw-btn-ghost mt-8 inline-flex">Shop Now</a>
            </div>
        </section>
    @endif

    {{-- Best Sellers --}}
    @if ($bestSellers->isNotEmpty())
        <section class="rtw-container py-16 lg:py-24">
            <div class="mb-10 flex items-end justify-between">
                <div>
                    <p class="rtw-label mb-3">Loved by Many</p>
                    <h2 class="font-serif text-3xl lg:text-4xl">Best Sellers</h2>
                </div>
                <a href="{{ route('shop.index') }}" wire:navigate
                    class="rtw-link-underline hidden text-xs font-medium tracking-widest uppercase sm:inline-block">View
                    All</a>
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-x-8">
                @foreach ($bestSellers as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- About teaser --}}
    @if ($about)
        <section class="rtw-container py-16 lg:py-24">
            <div class="mx-auto max-w-xl text-center">
                <p class="rtw-label mb-4">About</p>
                <h2 class="font-serif text-3xl lg:text-4xl">{{ $about->title }}</h2>
                <p class="mt-5 text-sm leading-relaxed text-neutral-600">
                    {{ \Illuminate\Support\Str::limit(strip_tags($about->content), 220) }}</p>
                <a href="{{ route('pages.about') }}" wire:navigate class="rtw-btn-secondary mt-8 inline-flex">Our
                    Story</a>
            </div>
        </section>
    @endif
</div>
