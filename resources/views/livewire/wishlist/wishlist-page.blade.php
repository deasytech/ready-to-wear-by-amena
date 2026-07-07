@php $currency = app(\App\Services\CurrencyService::class); @endphp

<div
    class="rtw-container py-16"
    @if (! auth()->check())
        x-data
        x-init="$wire.loadGuestIds(JSON.parse(localStorage.getItem('rtw_wishlist') || '[]'))"
        x-on:guest-wishlist-remove.window="
            let ids = JSON.parse(localStorage.getItem('rtw_wishlist') || '[]');
            ids = ids.filter(id => id !== $event.detail.productId);
            localStorage.setItem('rtw_wishlist', JSON.stringify(ids));
        "
    @endif
>
    <p class="rtw-label mb-4">Saved</p>
    <h1 class="font-serif text-3xl lg:text-4xl">Your Wishlist</h1>

    @if (! auth()->check())
        <p class="mt-4 max-w-md text-sm text-neutral-500">
            <a href="{{ route('login') }}" wire:navigate class="rtw-link-underline">Sign in</a> to keep your wishlist saved across devices.
        </p>
    @endif

    @if ($rows->isEmpty())
        <x-storefront.empty-state
            title="Your wishlist is empty"
            description="Save pieces you love and find them here anytime."
            actionLabel="Continue Shopping"
            :actionHref="route('shop.index')"
        />
    @else
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-x-8">
            @foreach ($rows as $row)
                <div>
                    <x-storefront.product-card :product="$row['product']" :wishlisted="true" />
                    <div class="mt-3 flex gap-3 text-xs">
                        <button type="button" wire:click="moveToBag({{ $row['product']->id }})" class="rtw-link-underline tracking-wide uppercase">Move to Bag</button>
                        @if ($row['wishlistItemId'])
                            <button type="button" wire:click="removeItem({{ $row['wishlistItemId'] }})" class="rtw-link-underline tracking-wide text-neutral-500 uppercase">Remove</button>
                        @else
                            <button type="button" wire:click="removeGuestProduct({{ $row['product']->id }})" class="rtw-link-underline tracking-wide text-neutral-500 uppercase">Remove</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
