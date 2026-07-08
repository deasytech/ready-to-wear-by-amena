<div x-data="{ mobileOpen: false, searchOpen: false, scrolled: false }" x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 24 })" @keydown.escape.window="mobileOpen = false; searchOpen = false">
    <header class="sticky top-0 z-40 border-b border-neutral-200 bg-white transition-shadow duration-300"
        :class="scrolled ? 'shadow-[0_1px_0_0_rgba(0,0,0,0.06)]' : ''">
        <div class="rtw-container flex h-16 items-center justify-between lg:h-20">
            {{-- Mobile: hamburger --}}
            <button type="button" class="rtw-focus -ml-2 flex size-10 items-center justify-center lg:hidden"
                aria-label="Open menu" @click="mobileOpen = true">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>

            {{-- Logo --}}
            <a href="{{ route('home') }}" wire:navigate class="flex shrink-0 items-center lg:flex-1">
                <img src="{{ asset('images/logo-black.png') }}" alt="Ready-To-Wear by Amena"
                    class="h-9 w-auto object-contain lg:h-11">
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden items-center gap-8 lg:flex" aria-label="Primary">
                <a href="{{ route('shop.index', ['sort' => 'newest']) }}" wire:navigate
                    class="rtw-link-underline text-xs font-medium tracking-[0.1em] uppercase">New Arrivals</a>
                <a href="{{ route('shop.index') }}" wire:navigate
                    class="rtw-link-underline text-xs font-medium tracking-[0.1em] uppercase">Shop</a>
                <div class="group relative">
                    <a href="{{ route('shop.index') }}" wire:navigate
                        class="rtw-link-underline text-xs font-medium tracking-[0.1em] uppercase">Collections</a>
                    @if ($navCollections->isNotEmpty())
                        <div
                            class="invisible absolute top-full left-1/2 z-50 w-56 -translate-x-1/2 border border-neutral-200 bg-white py-2 opacity-0 shadow-lg transition-all duration-150 group-hover:visible group-hover:opacity-100">
                            @foreach ($navCollections as $collection)
                                <a href="{{ route('collections.show', $collection) }}" wire:navigate
                                    class="block px-5 py-2 text-xs tracking-wide uppercase hover:bg-neutral-50">
                                    {{ $collection->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <a href="{{ route('pages.about') }}" wire:navigate
                    class="rtw-link-underline text-xs font-medium tracking-[0.1em] uppercase">About</a>
            </nav>

            {{-- Icons --}}
            <div class="flex items-center justify-end gap-1 lg:flex-1">
                <button type="button" class="rtw-focus flex size-10 items-center justify-center" aria-label="Search"
                    @click="searchOpen = true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>

                <a href="{{ auth()->check() ? route('account.overview') : route('login') }}" wire:navigate
                    class="rtw-focus hidden size-10 items-center justify-center lg:flex" aria-label="Account">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </a>

                <a href="{{ route('wishlist.index') }}" wire:navigate
                    class="rtw-focus relative flex size-10 items-center justify-center" aria-label="Wishlist"
                    x-data="{ guestCount: {{ auth()->check() ? 0 : 'null' }} }"
                    @if (!auth()->check()) x-init="guestCount = (JSON.parse(localStorage.getItem('rtw_wishlist') || '[]')).length"
                        @wishlist-toggle.window="guestCount = (JSON.parse(localStorage.getItem('rtw_wishlist') || '[]')).length" @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                    @if (auth()->check())
                        @if ($wishlistCount > 0)
                            <span
                                class="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-black text-[10px] text-white">{{ $wishlistCount }}</span>
                        @endif
                    @else
                        <span x-show="guestCount > 0" x-cloak x-text="guestCount"
                            class="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-black text-[10px] text-white"></span>
                    @endif
                </a>

                <a href="{{ route('cart.index') }}" @click.prevent="$dispatch('open-cart-drawer')"
                    class="rtw-focus relative flex size-10 items-center justify-center" aria-label="Shopping bag">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m-3 0h13.5l-1.125 10.125A2.25 2.25 0 0 1 15.383 22.5H8.618a2.25 2.25 0 0 1-2.243-1.875L5.25 10.5Z" />
                    </svg>
                    @if ($cartCount > 0)
                        <span
                            class="absolute top-1 right-1 flex size-4 items-center justify-center rounded-full bg-black text-[10px] text-white">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    {{-- Mobile drawer --}}
    <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" style="display: none;">
        <div class="fixed inset-0 bg-black/40" x-show="mobileOpen" x-transition.opacity @click="mobileOpen = false">
        </div>
        <div class="fixed inset-y-0 left-0 flex w-full max-w-xs flex-col overflow-y-auto bg-white shadow-xl"
            x-show="mobileOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full">
            <div class="flex items-center justify-between border-b border-neutral-200 px-5 py-4">
                <img src="{{ asset('images/logo-black.png') }}" alt="Ready-To-Wear by Amena"
                    class="h-8 w-auto object-contain">
                <button type="button" class="rtw-focus flex size-10 items-center justify-center"
                    aria-label="Close menu" @click="mobileOpen = false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="flex flex-1 flex-col px-5 py-4" aria-label="Mobile primary">
                <a href="{{ route('shop.index', ['sort' => 'newest']) }}" wire:navigate
                    class="border-b border-neutral-100 py-4 text-sm font-medium tracking-wide uppercase">New
                    Arrivals</a>
                <a href="{{ route('shop.index') }}" wire:navigate
                    class="border-b border-neutral-100 py-4 text-sm font-medium tracking-wide uppercase">Shop</a>
                @foreach ($navCollections as $collection)
                    <a href="{{ route('collections.show', $collection) }}" wire:navigate
                        class="border-b border-neutral-100 py-4 pl-4 text-sm tracking-wide text-neutral-600 uppercase">{{ $collection->name }}</a>
                @endforeach
                <a href="{{ route('pages.about') }}" wire:navigate
                    class="border-b border-neutral-100 py-4 text-sm font-medium tracking-wide uppercase">About</a>
                <a href="{{ auth()->check() ? route('account.overview') : route('login') }}" wire:navigate
                    class="border-b border-neutral-100 py-4 text-sm font-medium tracking-wide uppercase">Account</a>
                <a href="{{ route('wishlist.index') }}" wire:navigate
                    class="py-4 text-sm font-medium tracking-wide uppercase">Wishlist</a>
            </nav>
        </div>
    </div>

    {{-- Search overlay --}}
    <div x-show="searchOpen" x-cloak class="fixed inset-0 z-50" style="display: none;">
        <div class="fixed inset-0 bg-black/40" x-show="searchOpen" x-transition.opacity @click="searchOpen = false">
        </div>
        <div class="fixed inset-x-0 top-0 border-b border-neutral-200 bg-white" x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-y-4 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100">
            <div class="rtw-container flex items-center gap-4 py-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-neutral-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <form action="{{ route('search.index') }}" method="GET" class="flex-1">
                    <label for="header-search-input" class="sr-only">Search products</label>
                    <input type="search" id="header-search-input" name="q"
                        placeholder="Search products&hellip;"
                        class="rtw-focus w-full border-0 bg-transparent p-0 font-serif text-xl placeholder:text-neutral-400 focus:ring-0"
                        autofocus>
                </form>
                <button type="button" class="rtw-focus flex size-10 items-center justify-center"
                    aria-label="Close search" @click="searchOpen = false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
