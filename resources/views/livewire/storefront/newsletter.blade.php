<div>
    @if ($subscribed)
        <p class="text-sm text-white">Thank you &mdash; you're on the list.</p>
    @else
        <form wire:submit="subscribe" class="flex w-full max-w-md gap-3" novalidate>
            <label for="newsletter-email" class="sr-only">Email address</label>
            <input
                type="email"
                id="newsletter-email"
                wire:model="email"
                placeholder="Email address"
                class="rtw-focus w-full border border-white/30 bg-transparent px-4 py-3 text-sm text-white placeholder:text-white/50 focus:border-white"
            >
            <button type="submit" class="rtw-btn shrink-0 border border-white bg-white text-black hover:bg-black hover:text-white" wire:loading.attr="disabled">
                Subscribe
            </button>
        </form>
        @error('email')
            <p class="mt-2 text-xs text-white/70">{{ $message }}</p>
        @enderror
    @endif
</div>
