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
    <flux:heading size="xl" level="1">Order History</flux:heading>

    @if ($orders->isEmpty())
        <div class="rounded-xl border border-neutral-200 p-10 text-center dark:border-neutral-700">
            <flux:heading size="lg">No orders yet</flux:heading>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">When you place an order, it will show up here.</p>
            <flux:button href="{{ route('shop.index') }}" wire:navigate variant="primary" class="mt-6">Start Shopping</flux:button>
        </div>
    @else
        <div class="divide-y divide-neutral-200 rounded-xl border border-neutral-200 dark:divide-neutral-700 dark:border-neutral-700">
            @foreach ($orders as $order)
                <a href="{{ route('dashboard.orders.show', $order) }}" wire:navigate class="flex flex-wrap items-center justify-between gap-2 p-5 text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800">
                    <span class="font-medium">Order #{{ $order->id }}</span>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ $order->created_at->format('M j, Y') }}</span>
                    <span class="text-zinc-500 dark:text-zinc-400">{{ $order->items->count() }} item(s)</span>
                    <span>{{ $currency->formatForDisplay($order->grand_total, $order->currency) }}</span>
                    <flux:badge :color="$statusColors[$order->status] ?? 'zinc'" size="sm">{{ \App\Models\Order::STATUSES[$order->status] ?? ucfirst($order->status) }}</flux:badge>
                </a>
            @endforeach
        </div>

        <div>
            {{ $orders->links() }}
        </div>
    @endif
</div>
