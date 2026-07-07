<x-layouts.storefront :title="'Terms & Conditions'">
    <div class="rtw-container py-16 lg:py-24">
        <div class="max-w-2xl">
            <p class="rtw-label mb-4">Legal</p>
            <h1 class="font-serif text-3xl lg:text-4xl">Terms &amp; Conditions</h1>
            <p class="mt-4 text-xs text-neutral-500">Last updated {{ now()->format('F Y') }}</p>

            <div class="mt-10 space-y-8 text-sm leading-relaxed text-neutral-700">
                <section>
                    <h2 class="mb-2 font-serif text-xl text-black">Orders &amp; Payment</h2>
                    <p>All prices are listed in Nigerian Naira unless another currency is selected. Orders are confirmed once payment has been successfully verified. We reserve the right to cancel an order if a listed item is found to be out of stock after purchase, in which case a full refund will be issued.</p>
                </section>
                <section>
                    <h2 class="mb-2 font-serif text-xl text-black">Product Descriptions</h2>
                    <p>We make every effort to accurately describe and photograph our products. Slight variations in colour may occur due to screen display settings.</p>
                </section>
                <section>
                    <h2 class="mb-2 font-serif text-xl text-black">Limitation of Liability</h2>
                    <p>Ready To Wear by Amena is not liable for delivery delays caused by third-party courier partners once an order has been dispatched.</p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.storefront>
