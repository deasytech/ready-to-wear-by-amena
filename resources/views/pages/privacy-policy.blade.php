<x-layouts.storefront :title="'Privacy Policy'">
    <div class="rtw-container py-16 lg:py-24">
        <div class="max-w-2xl">
            <p class="rtw-label mb-4">Legal</p>
            <h1 class="font-serif text-3xl lg:text-4xl">Privacy Policy</h1>
            <p class="mt-4 text-xs text-neutral-500">Last updated {{ now()->format('F Y') }}</p>

            <div class="mt-10 space-y-8 text-sm leading-relaxed text-neutral-700">
                <section>
                    <h2 class="mb-2 font-serif text-xl text-black">Information We Collect</h2>
                    <p>When you create an account, place an order, or sign up for our newsletter, we collect your name, email address, phone number, and delivery address. Payment details are processed directly by our payment provider and are never stored on our servers.</p>
                </section>
                <section>
                    <h2 class="mb-2 font-serif text-xl text-black">How We Use Your Information</h2>
                    <p>We use your information to process orders, provide customer support, and, where you've opted in, send you updates about new collections. We do not sell your personal information to third parties.</p>
                </section>
                <section>
                    <h2 class="mb-2 font-serif text-xl text-black">Your Rights</h2>
                    <p>You may request a copy of the personal data we hold about you, ask us to correct it, or request that we delete your account at any time by contacting <a href="mailto:hello@readytowearbyamena.com" class="rtw-link-underline">hello@readytowearbyamena.com</a>.</p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.storefront>
