@php
    $currency = app(\App\Services\CurrencyService::class);
    $statusColors = [
        'pending' => 'zinc',
        'confirmed' => 'blue',
        'processing' => 'amber',
        'shipped' => 'sky',
        'delivered' => 'green',
        'cancelled' => 'red',
        'refunded' => 'red',
    ];
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">Welcome back, {{ auth()->user()->name }}</flux:heading>
        <flux:subheading>Here's what's happening with your orders and shipments.</flux:subheading>
    </div>

    <div class="grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Total Orders</p>
            <p class="mt-2 text-2xl font-semibold">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">In Transit</p>
            <p class="mt-2 text-2xl font-semibold">{{ $stats['in_transit'] }}</p>
        </div>
        <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Delivered</p>
            <p class="mt-2 text-2xl font-semibold">{{ $stats['delivered'] }}</p>
        </div>
        <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Saved Addresses</p>
            <p class="mt-2 text-2xl font-semibold">{{ $stats['addresses'] }}</p>
        </div>
    </div>

    @if ($activeShipments->isNotEmpty())
        <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700" wire:poll.30s>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">Active Shipments</flux:heading>
                <flux:button href="{{ route('dashboard.orders') }}" wire:navigate variant="ghost" size="sm">View all orders</flux:button>
            </div>
            <div class="space-y-5">
                @foreach ($activeShipments as $order)
                    <a href="{{ route('dashboard.orders.show', $order) }}" wire:navigate class="block rounded-lg border border-neutral-200 p-4 hover:border-blue-400 dark:border-neutral-700">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-sm font-medium">Order #{{ $order->id }}</span>
                            <flux:badge :color="$statusColors[$order->status] ?? 'zinc'" size="sm">{{ \App\Models\Order::STATUSES[$order->status] ?? ucfirst($order->status) }}</flux:badge>
                        </div>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $order->items->count() }} item(s) &middot; placed {{ $order->created_at->format('M j, Y') }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <a href="{{ route('dashboard.orders') }}" wire:navigate class="rounded-xl border border-neutral-200 p-6 hover:border-blue-400 dark:border-neutral-700">
            <flux:heading size="lg">Order History</flux:heading>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">View past orders and track shipments.</p>
        </a>
        <a href="{{ route('dashboard.addresses') }}" wire:navigate class="rounded-xl border border-neutral-200 p-6 hover:border-blue-400 dark:border-neutral-700">
            <flux:heading size="lg">Addresses</flux:heading>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Manage your saved delivery addresses.</p>
        </a>
        <a href="{{ route('wishlist.index') }}" wire:navigate class="rounded-xl border border-neutral-200 p-6 hover:border-blue-400 dark:border-neutral-700">
            <flux:heading size="lg">Wishlist</flux:heading>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Pieces you've saved for later.</p>
        </a>
    </div>

    @if ($recentOrders->isNotEmpty())
        <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">Recent Orders</flux:heading>
                <flux:button href="{{ route('dashboard.orders') }}" wire:navigate variant="ghost" size="sm">View all</flux:button>
            </div>
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach ($recentOrders as $order)
                    <a href="{{ route('dashboard.orders.show', $order) }}" wire:navigate class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        <span class="font-medium">Order #{{ $order->id }}</span>
                        <span class="text-zinc-500 dark:text-zinc-400">{{ $order->created_at->format('M j, Y') }}</span>
                        <span>{{ $currency->formatForDisplay($order->grand_total, $order->currency) }}</span>
                        <flux:badge :color="$statusColors[$order->status] ?? 'zinc'" size="sm">{{ \App\Models\Order::STATUSES[$order->status] ?? ucfirst($order->status) }}</flux:badge>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
