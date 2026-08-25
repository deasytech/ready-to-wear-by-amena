<div class="flex h-full w-full flex-1 flex-col gap-6">
    <flux:heading size="xl" level="1">{{ $heading ?? '' }}</flux:heading>

    <div class="flex items-start gap-10 max-md:flex-col">
        <nav class="w-full shrink-0 space-y-1 md:w-48" aria-label="Settings">
            <a href="{{ route('settings.profile') }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('settings.profile') ? 'bg-zinc-100 font-medium text-zinc-800 dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-700/50' }}">{{ __('Profile') }}</a>
            <a href="{{ route('settings.password') }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('settings.password') ? 'bg-zinc-100 font-medium text-zinc-800 dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-700/50' }}">{{ __('Password') }}</a>
            <a href="{{ route('settings.appearance') }}" wire:navigate class="block rounded-lg px-3 py-2 text-sm {{ request()->routeIs('settings.appearance') ? 'bg-zinc-100 font-medium text-zinc-800 dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-700/50' }}">{{ __('Appearance') }}</a>
        </nav>

        <div class="flex-1 self-stretch rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

            <div class="mt-6 w-full max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
