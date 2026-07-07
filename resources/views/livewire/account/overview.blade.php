<div class="rtw-container py-16">
    <p class="rtw-label mb-4">My Account</p>
    <h1 class="mb-10 font-serif text-3xl">Welcome back, {{ auth()->user()->name }}</h1>

    <x-account.nav />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('account.orders') }}" wire:navigate class="rtw-focus border border-neutral-200 p-6 hover:border-black">
            <h2 class="text-sm font-medium tracking-wide uppercase">Order History</h2>
            <p class="mt-2 text-sm text-neutral-500">View past orders and tracking.</p>
        </a>
        <a href="{{ route('account.addresses') }}" wire:navigate class="rtw-focus border border-neutral-200 p-6 hover:border-black">
            <h2 class="text-sm font-medium tracking-wide uppercase">Addresses</h2>
            <p class="mt-2 text-sm text-neutral-500">Manage your saved delivery addresses.</p>
        </a>
        <a href="{{ route('wishlist.index') }}" wire:navigate class="rtw-focus border border-neutral-200 p-6 hover:border-black">
            <h2 class="text-sm font-medium tracking-wide uppercase">Wishlist</h2>
            <p class="mt-2 text-sm text-neutral-500">Pieces you've saved for later.</p>
        </a>
        <a href="{{ route('settings.profile') }}" wire:navigate class="rtw-focus border border-neutral-200 p-6 hover:border-black">
            <h2 class="text-sm font-medium tracking-wide uppercase">Account Settings</h2>
            <p class="mt-2 text-sm text-neutral-500">Update your profile and password.</p>
        </a>
    </div>

    @if ($recentOrders->isNotEmpty())
        <div class="mt-14">
            <h2 class="rtw-label mb-4">Recent Orders</h2>
            <div class="divide-y divide-neutral-200 border-t border-b border-neutral-200">
                @foreach ($recentOrders as $order)
                    <a href="{{ route('account.orders.show', $order) }}" wire:navigate class="flex items-center justify-between py-4 text-sm hover:bg-neutral-50">
                        <span>Order #{{ $order->id }}</span>
                        <span class="text-neutral-500">{{ $order->created_at->format('M j, Y') }}</span>
                        <span class="rtw-label">{{ $order->status }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
