<nav class="mb-10 flex flex-wrap gap-x-8 gap-y-2 border-b border-neutral-200 pb-4 text-sm" aria-label="Account">
    <a href="{{ route('account.overview') }}" wire:navigate class="{{ request()->routeIs('account.overview') ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">Overview</a>
    <a href="{{ route('account.orders') }}" wire:navigate class="{{ request()->routeIs('account.orders*') ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">Orders</a>
    <a href="{{ route('account.addresses') }}" wire:navigate class="{{ request()->routeIs('account.addresses') ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">Addresses</a>
    <a href="{{ route('wishlist.index') }}" wire:navigate class="{{ request()->routeIs('wishlist.index') ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">Wishlist</a>
    <a href="{{ route('settings.profile') }}" wire:navigate class="{{ request()->routeIs('settings.*') ? 'font-medium text-black' : 'text-neutral-500 hover:text-black' }}">Settings</a>
</nav>
