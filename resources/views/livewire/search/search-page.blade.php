<div class="rtw-container py-10 lg:py-14">
    <div class="mx-auto max-w-xl">
        <label for="search-input" class="sr-only">Search</label>
        <div class="flex items-center gap-3 border-b-2 border-black pb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                type="search"
                id="search-input"
                wire:model.live.debounce.400ms="query"
                placeholder="Search for products, categories&hellip;"
                class="w-full border-0 bg-transparent p-0 font-serif text-2xl placeholder:text-neutral-400 focus:ring-0"
                autofocus
            >
        </div>
    </div>

    <div class="mt-12">
        @if (strlen(trim($query)) < 2)
            <p class="text-center text-sm text-neutral-500">Start typing to search the collection.</p>
        @elseif ($results->isEmpty())
            <x-storefront.empty-state
                title="No results for &ldquo;{{ $query }}&rdquo;"
                description="Try a different search term, or browse the full shop."
                actionLabel="Shop All"
                actionHref="{{ route('shop.index') }}"
            />
        @else
            <p class="rtw-label mb-8 text-center">{{ $results->total() }} results for &ldquo;{{ $query }}&rdquo;</p>
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-x-8">
                @foreach ($results as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-14">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
