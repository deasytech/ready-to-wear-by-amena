@php
    $currency = app(\App\Services\CurrencyService::class);
@endphp

<div class="rtw-container py-16">
    <p class="rtw-label mb-4"><a href="{{ route('account.orders') }}" wire:navigate class="rtw-link-underline">Order History</a> / Order #{{ $order->id }}</p>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="font-serif text-3xl">Order #{{ $order->id }}</h1>
        <span class="rtw-label border border-neutral-200 px-3 py-1.5">{{ ucfirst($order->status) }}</span>
    </div>
    <p class="mt-2 text-sm text-neutral-500">Placed on {{ $order->created_at->format('F j, Y') }}</p>

    <div class="mt-10 grid gap-10 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="divide-y divide-neutral-200 border-t border-b border-neutral-200">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-4 py-5">
                        <div>
                            <p class="text-sm font-medium">{{ $item->name }}</p>
                            @if ($item->color || $item->size)
                                <p class="text-xs text-neutral-500">
                                    {{ collect([$item->color, $item->size])->filter()->implode(' / ') }}
                                </p>
                            @endif
                            <p class="text-xs text-neutral-500">Qty {{ $item->quantity }}</p>
                        </div>
                        <p class="text-sm">{{ $currency->formatForDisplay($item->total_amount, $order->currency) }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="rtw-label mb-4">Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-neutral-500">Subtotal</dt><dd>{{ $currency->formatForDisplay($order->subtotal ?? $order->grand_total, $order->currency) }}</dd></div>
                <div class="flex justify-between"><dt class="text-neutral-500">Shipping</dt><dd>{{ $currency->formatForDisplay($order->shipping_amount ?? 0, $order->currency) }}</dd></div>
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between"><dt class="text-neutral-500">Discount</dt><dd>-{{ $currency->formatForDisplay($order->discount_amount, $order->currency) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-neutral-200 pt-2 font-medium"><dt>Total</dt><dd>{{ $currency->formatForDisplay($order->grand_total, $order->currency) }}</dd></div>
            </dl>

            @if ($order->address)
                <h2 class="rtw-label mt-8 mb-4">Shipping Address</h2>
                <address class="text-sm text-neutral-600 not-italic">
                    {{ $order->address->full_name }}<br>
                    {{ $order->address->street_address }}<br>
                    {{ $order->address->city }}, {{ $order->address->state }}<br>
                    {{ $order->address->country }}
                </address>
            @endif
        </div>
    </div>
</div>
