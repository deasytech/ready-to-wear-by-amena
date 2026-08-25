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
    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('dashboard.orders') }}" wire:navigate>Order History</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Order #{{ $order->id }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl" level="1">Order #{{ $order->id }}</flux:heading>
        <flux:badge :color="$statusColors[$order->status] ?? 'zinc'">{{ \App\Models\Order::STATUSES[$order->status] ?? ucfirst($order->status) }}</flux:badge>
    </div>
    <p class="-mt-4 text-sm text-zinc-500 dark:text-zinc-400">Placed on {{ $order->created_at->format('F j, Y') }}</p>

    <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-5">Shipment Tracking</flux:heading>
        <x-user-dashboard.shipment-tracker :order="$order" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-neutral-200 p-5 lg:col-span-2 dark:border-neutral-700">
            <flux:heading size="lg" class="mb-4">Items</flux:heading>
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-4 py-4">
                        <div>
                            <p class="text-sm font-medium">{{ $item->name }}</p>
                            @if ($item->color || $item->size)
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ collect([$item->color, $item->size])->filter()->implode(' / ') }}
                                </p>
                            @endif
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Qty {{ $item->quantity }}</p>
                        </div>
                        <p class="text-sm">{{ $currency->formatForDisplay($item->total_amount, $order->currency) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 p-5 dark:border-neutral-700">
            <flux:heading size="lg" class="mb-4">Summary</flux:heading>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500 dark:text-zinc-400">Subtotal</dt><dd>{{ $currency->formatForDisplay($order->subtotal ?? $order->grand_total, $order->currency) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500 dark:text-zinc-400">Shipping</dt><dd>{{ $currency->formatForDisplay($order->shipping_amount ?? 0, $order->currency) }}</dd></div>
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between"><dt class="text-zinc-500 dark:text-zinc-400">Discount</dt><dd>-{{ $currency->formatForDisplay($order->discount_amount, $order->currency) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-neutral-200 pt-2 font-medium dark:border-neutral-700"><dt>Total</dt><dd>{{ $currency->formatForDisplay($order->grand_total, $order->currency) }}</dd></div>
            </dl>

            @if ($order->address)
                <flux:heading size="lg" class="mt-6 mb-3">Shipping Address</flux:heading>
                <address class="text-sm text-zinc-600 not-italic dark:text-zinc-400">
                    {{ $order->address->full_name }}<br>
                    {{ $order->address->street_address }}<br>
                    {{ $order->address->city }}, {{ $order->address->state }}<br>
                    {{ $order->address->country }}
                </address>
            @endif
        </div>
    </div>
</div>
