<div class="rtw-container py-16">
    <p class="rtw-label mb-4">My Account</p>
    <h1 class="mb-10 font-serif text-3xl">Order History</h1>

    <x-account.nav />

    @if ($orders->isEmpty())
        <x-storefront.empty-state
            title="No orders yet"
            description="When you place an order, it will show up here."
            action-label="Start Shopping"
            :action-href="route('shop.index')"
        />
    @else
        <div class="mt-10 divide-y divide-neutral-200 border-t border-b border-neutral-200">
            @foreach ($orders as $order)
                <a href="{{ route('account.orders.show', $order) }}" wire:navigate class="flex flex-wrap items-center justify-between gap-2 py-5 text-sm hover:bg-neutral-50">
                    <span class="font-medium">Order #{{ $order->id }}</span>
                    <span class="text-neutral-500">{{ $order->created_at->format('M j, Y') }}</span>
                    <span class="text-neutral-500">{{ $order->items->count() }} item(s)</span>
                    <span class="rtw-label">{{ $order->status }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif
</div>
