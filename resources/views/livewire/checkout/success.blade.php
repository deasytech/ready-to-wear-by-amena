<div class="rtw-container py-16">
    @if ($order)
        <div class="mx-auto max-w-xl text-center">
            <p class="rtw-label mb-4">Order Confirmed</p>
            <h1 class="font-serif text-3xl">Thank you, your order is in.</h1>
            <p class="mt-4 text-neutral-600">Order #{{ $order->id }} has been received. A confirmation email is on its way to you.</p>
            <a href="{{ route('shop.index') }}" wire:navigate class="rtw-btn-primary mt-8 inline-flex">Continue Shopping</a>
        </div>
    @else
        <x-storefront.empty-state
            title="No order found"
            description="We couldn't find a recent order for this session."
            action-label="Back to Shop"
            :action-href="route('shop.index')"
        />
    @endif
</div>
