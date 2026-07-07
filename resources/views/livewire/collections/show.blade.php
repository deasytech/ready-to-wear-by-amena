<div>
    {{-- Hero --}}
    <section class="relative flex h-[50vh] min-h-[360px] items-end overflow-hidden bg-neutral-900 text-white">
        @if ($collection->image_url)
            <img src="{{ $collection->image_url }}" alt="{{ $collection->name }}" class="absolute inset-0 size-full object-cover">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
        <div class="rtw-container relative pb-12">
            <p class="rtw-label mb-3 text-white/80">Collection</p>
            <h1 class="font-serif text-4xl lg:text-5xl">{{ $collection->name }}</h1>
            @if ($collection->description)
                <p class="mt-4 max-w-xl text-sm text-white/80 lg:text-base">{{ $collection->description }}</p>
            @endif
        </div>
    </section>

    <div class="rtw-container py-10 lg:py-14">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4 border-b border-neutral-200 pb-6">
            <p class="rtw-label">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('Piece', $products->total()) }}</p>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($availableSizes as $size)
                        <button
                            type="button"
                            wire:click="toggleSize('{{ $size->slug }}')"
                            class="flex size-8 items-center justify-center border text-xs {{ in_array($size->slug, $sizes) ? 'border-black bg-black text-white' : 'border-neutral-300 hover:border-black' }}"
                        >
                            {{ $size->name }}
                        </button>
                    @endforeach
                </div>

                <select wire:model.live="availability" class="rtw-focus border border-neutral-300 bg-white px-3 py-2 text-xs tracking-wide uppercase">
                    <option value="all">All</option>
                    <option value="in_stock">In Stock</option>
                    <option value="sale">On Sale</option>
                </select>

                <select wire:model.live="sort" class="rtw-focus border border-neutral-300 bg-white px-3 py-2 text-xs tracking-wide uppercase">
                    <option value="newest">Featured</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="popularity">Popularity</option>
                </select>
            </div>
        </div>

        @if ($products->isEmpty())
            <x-storefront.empty-state title="No pieces match your filters" description="Try adjusting or clearing your filters." />
        @else
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-x-8">
                @foreach ($products as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-14">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
