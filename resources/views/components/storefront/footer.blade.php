<footer class="bg-black text-white">
    <div class="rtw-container py-16">
        <div class="mb-14 flex flex-col justify-between gap-8 border-b border-white/15 pb-14 lg:flex-row lg:items-end">
            <div>
                <p class="font-serif text-2xl">Join the world of Ready-To-Wear by Amena</p>
                <p class="mt-2 text-sm text-white/60">New collections, early access, and stories from the studio.</p>
            </div>
            <livewire:storefront.newsletter />
        </div>

        <div class="grid grid-cols-2 gap-10 lg:grid-cols-5">
            <div>
                <h3 class="rtw-label mb-5 text-white/50">Shop</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('shop.index', ['sort' => 'newest']) }}" wire:navigate
                            class="rtw-link-underline">New Arrivals</a></li>
                    <li><a href="{{ route('shop.index') }}" wire:navigate class="rtw-link-underline">Shop All</a></li>
                    <li><a href="{{ route('shop.index', ['availability' => 'sale']) }}" wire:navigate
                            class="rtw-link-underline">Sale</a></li>
                </ul>
            </div>

            <div>
                <h3 class="rtw-label mb-5 text-white/50">Customer Care</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('pages.contact') }}" wire:navigate class="rtw-link-underline">Contact</a></li>
                    <li><a href="{{ route('pages.shipping-returns') }}" wire:navigate
                            class="rtw-link-underline">Shipping &amp; Returns</a></li>
                    <li><a href="{{ route('wishlist.index') }}" wire:navigate class="rtw-link-underline">Wishlist</a>
                    </li>
                </ul>
            </div>

            <div>
                <h3 class="rtw-label mb-5 text-white/50">About</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('pages.about') }}#our-story" wire:navigate class="rtw-link-underline">Our
                            Story</a></li>
                    <li><a href="{{ route('pages.about') }}#our-culture" wire:navigate class="rtw-link-underline">Our
                            Culture</a></li>
                </ul>
            </div>

            <div>
                <h3 class="rtw-label mb-5 text-white/50">Legal</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('pages.privacy-policy') }}" wire:navigate class="rtw-link-underline">Privacy
                            Policy</a></li>
                    <li><a href="{{ route('pages.terms-conditions') }}" wire:navigate class="rtw-link-underline">Terms
                            &amp; Conditions</a></li>
                </ul>
            </div>

            <div>
                <h3 class="rtw-label mb-5 text-white/50">Follow</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="https://instagram.com/readytowearbyamena" target="_blank" rel="noopener"
                            class="rtw-link-underline">Instagram</a></li>
                </ul>
            </div>
        </div>

        <div
            class="mt-14 flex flex-col gap-4 border-t border-white/15 pt-8 text-xs text-white/50 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} Ready-To-Wear by Amena. All rights reserved.</p>
            <p>Designed for Nigeria, made to travel further.</p>
        </div>
    </div>
</footer>
