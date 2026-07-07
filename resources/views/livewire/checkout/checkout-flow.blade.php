@php $currency = app(\App\Services\CurrencyService::class); @endphp

<div class="rtw-container py-10 lg:py-14">
    @if ($cart->items->isEmpty())
        <x-storefront.empty-state
            title="Your bag is empty"
            description="Add something to your bag before checking out."
            actionLabel="Continue Shopping"
            :actionHref="route('shop.index')"
        />
    @else
        <div class="mb-10 flex items-center gap-2 text-xs tracking-widest uppercase">
            @foreach (['Information', 'Delivery', 'Shipping', 'Payment', 'Review'] as $index => $label)
                @php $num = $index + 1; @endphp
                <button type="button" wire:click="goToStep({{ $num }})" class="flex items-center gap-2 {{ $step === $num ? 'text-black' : 'text-neutral-400' }}" @if ($num >= $step) disabled @endif>
                    <span class="flex size-6 items-center justify-center rounded-full border {{ $step >= $num ? 'border-black bg-black text-white' : 'border-neutral-300' }}">{{ $num }}</span>
                    <span class="hidden sm:inline">{{ $label }}</span>
                </button>
                @if (! $loop->last)
                    <span class="mx-1 h-px w-4 bg-neutral-300 sm:w-8"></span>
                @endif
            @endforeach
        </div>

        <div class="grid gap-12 lg:grid-cols-3">
            <div class="lg:col-span-2">
                {{-- Step 1: Customer Information --}}
                @if ($step === 1)
                    <div>
                        <h2 class="font-serif text-2xl">Customer Information</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="rtw-label">First Name</label>
                                <input type="text" id="first_name" wire:model="first_name" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="last_name" class="rtw-label">Last Name</label>
                                <input type="text" id="last_name" wire:model="last_name" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email" class="rtw-label">Email</label>
                                <input type="email" id="email" wire:model="email" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="phone" class="rtw-label">Phone</label>
                                <input type="text" id="phone" wire:model="phone" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <button type="button" wire:click="nextStep" class="rtw-btn-primary mt-8">Continue to Delivery</button>
                    </div>
                @endif

                {{-- Step 2: Delivery Address --}}
                @if ($step === 2)
                    <div>
                        <h2 class="font-serif text-2xl">Delivery Address</h2>
                        <div class="mt-6 grid gap-4">
                            <div>
                                <label for="street_address" class="rtw-label">Street Address</label>
                                <input type="text" id="street_address" wire:model="street_address" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                @error('street_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="city" class="rtw-label">City</label>
                                    <input type="text" id="city" wire:model="city" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                    @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="state" class="rtw-label">State</label>
                                    <input type="text" id="state" wire:model="state" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                    @error('state') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="zip_code" class="rtw-label">Postal Code (optional)</label>
                                    <input type="text" id="zip_code" wire:model="zip_code" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                </div>
                                <div>
                                    <label for="country" class="rtw-label">Country</label>
                                    <input type="text" id="country" wire:model="country" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm">
                                    @error('country') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="notes" class="rtw-label">Delivery Notes (optional)</label>
                                <textarea id="notes" wire:model="notes" rows="2" class="rtw-focus mt-2 w-full border border-neutral-300 px-3 py-2.5 text-sm"></textarea>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" wire:click="previousStep" class="rtw-btn-secondary">Back</button>
                            <button type="button" wire:click="nextStep" class="rtw-btn-primary">Continue to Shipping</button>
                        </div>
                    </div>
                @endif

                {{-- Step 3: Delivery Method --}}
                @if ($step === 3)
                    <div>
                        <h2 class="font-serif text-2xl">Delivery Method</h2>
                        <div class="mt-6 space-y-3">
                            @foreach ($shippingMethods as $method)
                                <label class="flex cursor-pointer items-center justify-between border border-neutral-300 p-4 has-[:checked]:border-black">
                                    <span class="flex items-center gap-3">
                                        <input type="radio" wire:model="shipping_method_id" value="{{ $method->id }}" class="text-black focus:ring-black">
                                        <span>
                                            <span class="block text-sm font-medium">{{ $method->name }}</span>
                                            @if ($method->estimated_delivery)
                                                <span class="block text-xs text-neutral-500">{{ $method->estimated_delivery }}</span>
                                            @endif
                                        </span>
                                    </span>
                                    <span class="text-sm">{{ $currency->formatForDisplay($method->cost, $activeCurrency) }}</span>
                                </label>
                            @endforeach
                            @error('shipping_method_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" wire:click="previousStep" class="rtw-btn-secondary">Back</button>
                            <button type="button" wire:click="nextStep" class="rtw-btn-primary">Continue to Payment</button>
                        </div>
                    </div>
                @endif

                {{-- Step 4: Payment --}}
                @if ($step === 4)
                    <div>
                        <h2 class="font-serif text-2xl">Payment</h2>
                        <div class="mt-6 space-y-3">
                            <label class="flex cursor-pointer items-center gap-3 border border-neutral-300 p-4 has-[:checked]:border-black">
                                <input type="radio" wire:model="payment_method" value="paystack" class="text-black focus:ring-black">
                                <span class="text-sm font-medium">Pay Online (Card, Bank Transfer, USSD via Paystack)</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 border border-neutral-300 p-4 has-[:checked]:border-black">
                                <input type="radio" wire:model="payment_method" value="cod" class="text-black focus:ring-black">
                                <span class="text-sm font-medium">Cash on Delivery</span>
                            </label>
                            @error('payment_method') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" wire:click="previousStep" class="rtw-btn-secondary">Back</button>
                            <button type="button" wire:click="nextStep" class="rtw-btn-primary">Review Order</button>
                        </div>
                    </div>
                @endif

                {{-- Step 5: Review --}}
                @if ($step === 5)
                    <div>
                        <h2 class="font-serif text-2xl">Review Your Order</h2>

                        <div class="mt-6 space-y-6 text-sm">
                            <div class="border border-neutral-200 p-4">
                                <p class="rtw-label mb-2">Contact</p>
                                <p>{{ $first_name }} {{ $last_name }} &mdash; {{ $email }} &mdash; {{ $phone }}</p>
                            </div>
                            <div class="border border-neutral-200 p-4">
                                <p class="rtw-label mb-2">Deliver To</p>
                                <p>{{ $street_address }}, {{ $city }}, {{ $state }}, {{ $country }}</p>
                            </div>
                            <div class="border border-neutral-200 p-4">
                                <p class="rtw-label mb-2">Payment</p>
                                <p>{{ $payment_method === 'cod' ? 'Cash on Delivery' : 'Pay Online via Paystack' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <label for="discount_code" class="sr-only">Promo code</label>
                            <input type="text" id="discount_code" wire:model="discount_code" placeholder="Promo code" class="rtw-focus w-full max-w-xs border border-neutral-300 px-3 py-2.5 text-sm uppercase">
                            <button type="button" wire:click="applyDiscount" class="rtw-btn-secondary">Apply</button>
                        </div>
                        @if ($discountError)
                            <p class="mt-2 text-xs text-red-600">{{ $discountError }}</p>
                        @endif

                        @error('stock') <p class="mt-4 text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="mt-8 flex gap-3">
                            <button type="button" wire:click="previousStep" class="rtw-btn-secondary">Back</button>
                            <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" class="rtw-btn-primary">
                                <span wire:loading.remove wire:target="placeOrder">Place Order</span>
                                <span wire:loading wire:target="placeOrder">Processing&hellip;</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Order Summary --}}
            <div>
                <h2 class="rtw-label mb-4">Order Summary</h2>
                <div class="divide-y divide-neutral-200 border-t border-neutral-200">
                    @foreach ($cart->items as $item)
                        <div class="flex gap-4 py-4">
                            <div class="size-16 shrink-0 bg-neutral-100">
                                <img src="{{ $item->product->first_image }}" alt="{{ $item->product->name }}" class="size-full object-cover">
                            </div>
                            <div class="flex-1">
                                <p class="text-sm">{{ $item->product->name }}</p>
                                <p class="text-xs text-neutral-500">Qty {{ $item->quantity }}</p>
                            </div>
                            <p class="text-sm">{{ $currency->formatForDisplay($item->unit_price * $item->quantity, $activeCurrency) }}</p>
                        </div>
                    @endforeach
                </div>

                <dl class="mt-4 space-y-2 border-t border-neutral-200 pt-4 text-sm">
                    <div class="flex justify-between"><dt class="text-neutral-500">Subtotal</dt><dd>{{ $currency->formatForDisplay($cart->subtotal, $activeCurrency) }}</dd></div>
                    @if ($discountAmount > 0)
                        <div class="flex justify-between"><dt class="text-neutral-500">Discount</dt><dd>-{{ $currency->formatForDisplay($discountAmount, $activeCurrency) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-neutral-500">Shipping</dt><dd>{{ $currency->formatForDisplay($shippingCost, $activeCurrency) }}</dd></div>
                    <div class="flex justify-between border-t border-neutral-200 pt-2 text-base font-medium"><dt>Total</dt><dd>{{ $currency->formatForDisplay($grandTotal, $activeCurrency) }}</dd></div>
                </dl>
            </div>
        </div>
    @endif
</div>
