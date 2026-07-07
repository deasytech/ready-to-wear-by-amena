<div class="rtw-container py-16">
    <x-storefront.empty-state
        title="Payment cancelled"
        description="{{ session('error', 'Your payment was not completed. Your bag has been kept safe.') }}"
        action-label="Return to Bag"
        :action-href="route('cart.index')"
    />
</div>
