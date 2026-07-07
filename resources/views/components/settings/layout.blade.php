<div class="rtw-container py-16">
    <p class="rtw-label mb-4"><a href="{{ route('account.overview') }}" wire:navigate class="rtw-link-underline">My Account</a> / Settings</p>

    <div class="flex items-start gap-12 max-md:flex-col">
        <nav class="w-full shrink-0 space-y-1 md:w-48" aria-label="Settings">
            <a href="{{ route('settings.profile') }}" wire:navigate class="block border-b border-neutral-100 py-3 text-sm {{ request()->routeIs('settings.profile') ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">{{ __('Profile') }}</a>
            <a href="{{ route('settings.password') }}" wire:navigate class="block border-b border-neutral-100 py-3 text-sm {{ request()->routeIs('settings.password') ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">{{ __('Password') }}</a>
        </nav>

        <div class="flex-1 self-stretch">
            <h1 class="font-serif text-2xl">{{ $heading ?? '' }}</h1>
            <p class="mt-1 text-sm text-neutral-500">{{ $subheading ?? '' }}</p>

            <div class="mt-8 w-full max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
