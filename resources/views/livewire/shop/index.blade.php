<div class="rtw-container py-10 lg:py-14">
    <div class="mb-8 flex items-end justify-between border-b border-neutral-200 pb-6">
        <div>
            <p class="rtw-label mb-3">
                @if ($category)
                    {{ $categories->firstWhere('slug', $category)?->name ?? 'Shop' }}
                @else
                    Shop All
                @endif
            </p>
            <h1 class="font-serif text-3xl lg:text-4xl">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('Piece', $products->total()) }}</h1>
        </div>

        {{-- Mobile trigger buttons --}}
        <div class="flex gap-2 lg:hidden">
            <button type="button" class="rtw-btn-secondary" wire:click="$set('showFilters', true)">Filter</button>
            <button type="button" class="rtw-btn-secondary" wire:click="$set('showSort', true)">Sort</button>
        </div>

        {{-- Desktop sort --}}
        <div class="hidden items-center gap-3 lg:flex">
            <label for="sort-desktop" class="rtw-label">Sort by</label>
            <select id="sort-desktop" wire:model.live="sort" class="rtw-focus border border-neutral-300 bg-white px-3 py-2 text-xs tracking-wide uppercase">
                <option value="newest">Newest</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="popularity">Popularity</option>
            </select>
        </div>
    </div>

    <div class="grid gap-10 lg:grid-cols-[240px_1fr]">
        {{-- Desktop filters --}}
        <aside class="hidden lg:block">
            <div class="space-y-8">
                <div>
                    <h2 class="rtw-label mb-4">Category</h2>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <button type="button" wire:click="$set('category', null)" class="{{ ! $category ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">
                                All
                            </button>
                        </li>
                        @foreach ($categories as $cat)
                            <li>
                                <button type="button" wire:click="$set('category', '{{ $cat->slug }}')" class="{{ $category === $cat->slug ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">
                                    {{ $cat->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h2 class="rtw-label mb-4">Size</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($availableSizes as $size)
                            <button
                                type="button"
                                wire:click="toggleSize('{{ $size->slug }}')"
                                class="flex size-9 items-center justify-center border text-xs {{ in_array($size->slug, $sizes) ? 'border-black bg-black text-white' : 'border-neutral-300 text-black hover:border-black' }}"
                            >
                                {{ $size->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h2 class="rtw-label mb-4">Price</h2>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model.live.debounce.500ms="minPrice" placeholder="Min" class="rtw-focus w-full border border-neutral-300 px-3 py-2 text-sm">
                        <span class="text-neutral-400">&ndash;</span>
                        <input type="number" wire:model.live.debounce.500ms="maxPrice" placeholder="Max" class="rtw-focus w-full border border-neutral-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <h2 class="rtw-label mb-4">Availability</h2>
                    <div class="space-y-2 text-sm">
                        @foreach (['all' => 'All', 'in_stock' => 'In Stock', 'sale' => 'On Sale'] as $value => $label)
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model.live="availability" value="{{ $value }}" class="border-neutral-300 text-black focus:ring-black">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if ($category || ! empty($sizes) || $minPrice || $maxPrice || $availability !== 'all')
                    <button type="button" wire:click="clearFilters" class="rtw-link-underline text-xs tracking-wide uppercase">Clear all filters</button>
                @endif
            </div>
        </aside>

        {{-- Grid --}}
        <div>
            @if ($products->isEmpty())
                <x-storefront.empty-state
                    title="No pieces match your filters"
                    description="Try adjusting or clearing your filters."
                    actionLabel="Clear Filters"
                    actionHref="{{ route('shop.index') }}"
                />
            @else
                <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
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

    {{-- Mobile filter bottom sheet --}}
    <div
        x-data
        x-show="$wire.showFilters"
        x-cloak
        class="fixed inset-0 z-50 lg:hidden"
        style="display: none"
    >
        <div class="fixed inset-0 bg-black/40" wire:click="$set('showFilters', false)"></div>
        <div class="fixed inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-2xl bg-white p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="font-serif text-xl">Filter</h2>
                <button type="button" wire:click="$set('showFilters', false)" class="rtw-focus flex size-9 items-center justify-center" aria-label="Close filters">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="space-y-8">
                <div>
                    <h3 class="rtw-label mb-4">Category</h3>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="$set('category', null)" class="rtw-btn-secondary !px-4 !py-2 {{ ! $category ? '!bg-black !text-white' : '' }}">All</button>
                        @foreach ($categories as $cat)
                            <button type="button" wire:click="$set('category', '{{ $cat->slug }}')" class="rtw-btn-secondary !px-4 !py-2 {{ $category === $cat->slug ? '!bg-black !text-white' : '' }}">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="rtw-label mb-4">Size</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($availableSizes as $size)
                            <button type="button" wire:click="toggleSize('{{ $size->slug }}')" class="flex size-10 items-center justify-center border text-xs {{ in_array($size->slug, $sizes) ? 'border-black bg-black text-white' : 'border-neutral-300' }}">{{ $size->name }}</button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="rtw-label mb-4">Availability</h3>
                    <div class="space-y-2 text-sm">
                        @foreach (['all' => 'All', 'in_stock' => 'In Stock', 'sale' => 'On Sale'] as $value => $label)
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model.live="availability" value="{{ $value }}" class="border-neutral-300 text-black focus:ring-black">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="button" wire:click="$set('showFilters', false)" class="rtw-btn-primary w-full">Show {{ $products->total() }} Results</button>
            </div>
        </div>
    </div>

    {{-- Mobile sort bottom sheet --}}
    <div
        x-data
        x-show="$wire.showSort"
        x-cloak
        class="fixed inset-0 z-50 lg:hidden"
        style="display: none"
    >
        <div class="fixed inset-0 bg-black/40" wire:click="$set('showSort', false)"></div>
        <div class="fixed inset-x-0 bottom-0 rounded-t-2xl bg-white p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="font-serif text-xl">Sort By</h2>
                <button type="button" wire:click="$set('showSort', false)" class="rtw-focus flex size-9 items-center justify-center" aria-label="Close sort">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="space-y-1">
                @foreach (['newest' => 'Newest', 'price_low' => 'Price: Low to High', 'price_high' => 'Price: High to Low', 'popularity' => 'Popularity'] as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('sort', '{{ $value }}'); $set('showSort', false)"
                        class="flex w-full items-center justify-between border-b border-neutral-100 py-3 text-sm {{ $sort === $value ? 'font-medium text-black' : 'text-neutral-500' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
