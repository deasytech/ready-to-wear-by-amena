<?php

namespace App\Livewire\Checkout;

use App\Mail\OrderPlaced;
use App\Models\CompanyAddress;
use App\Models\DiscountCode;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CurrencyService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ShipBubbleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
#[Title('Checkout')]
class CheckoutFlow extends Component
{
    public int $step = 1;

    // Step 1: customer information
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    // Step 2: delivery address
    public string $street_address = '';

    public string $city = '';

    public string $state = '';

    public string $zip_code = '';

    public string $country = 'Nigeria';

    public string $notes = '';

    // Step 3: delivery method - always a live ShipBubble courier rate, indexing into $liveCouriers.
    public ?int $selectedCourierIndex = null;

    public array $liveCouriers = [];

    public ?string $shippingError = null;

    // A fetch_rates request_token is single-use and tied to that specific
    // quote - needed later to actually book the chosen courier.
    public ?string $shippingRequestToken = null;

    // Step 4: payment
    public string $payment_method = 'paystack';

    // Step 5: discount
    public string $discount_code = '';

    public ?string $discountError = null;

    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->email = $user->email;
            $names = explode(' ', $user->name, 2);
            $this->first_name = $names[0] ?? '';
            $this->last_name = $names[1] ?? '';

            $default = $user->addresses()->whereNull('order_id')->where('is_default', true)->first()
                ?? $user->addresses()->whereNull('order_id')->first();

            if ($default) {
                $this->phone = $default->phone ?? '';
                $this->street_address = $default->street_address ?? '';
                $this->city = $default->city ?? '';
                $this->state = $default->state ?? '';
                $this->zip_code = $default->zip_code ?? '';
                $this->country = $default->country ?? 'Nigeria';
            }
        }
    }

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:30',
            ],
            2 => [
                'street_address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'zip_code' => 'nullable|string|max:20',
            ],
            3 => ['selectedCourierIndex' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (! array_key_exists($value, $this->liveCouriers)) {
                        $fail('Please select a shipping option.');
                    }
                },
            ]],
            4 => ['payment_method' => 'required|in:paystack,cod'],
            default => [],
        };
    }

    public function nextStep(ShipBubbleService $shipBubble, CartService $cartService, CurrencyService $currencyService): void
    {
        $this->validate($this->rulesForStep($this->step));

        if ($this->step === 2) {
            $this->fetchLiveRates($shipBubble, $cartService, $currencyService);
        }

        $this->step = min(5, $this->step + 1);
    }

    /**
     * Retry fetching courier rates for the address already on file, without
     * re-validating the earlier steps. Used by the "Retry" action on step 3
     * when ShipBubble was unavailable or returned no couriers.
     */
    public function retryShipping(ShipBubbleService $shipBubble, CartService $cartService, CurrencyService $currencyService): void
    {
        $this->fetchLiveRates($shipBubble, $cartService, $currencyService);
    }

    /**
     * Validate the customer's delivery address with ShipBubble and fetch live
     * courier rates for it. ShipBubble always prices in NGN, so each rate is
     * converted to the cart's active currency for display and checkout math.
     */
    protected function fetchLiveRates(ShipBubbleService $shipBubble, CartService $cartService, CurrencyService $currencyService): void
    {
        $this->liveCouriers = [];
        $this->selectedCourierIndex = null;
        $this->shippingError = null;
        $this->shippingRequestToken = null;

        $pickup = CompanyAddress::whereNotNull('address_code')->first();

        if (! $pickup) {
            $this->shippingError = 'Shipping is not available right now - no pickup address is configured. Please contact support.';

            return;
        }

        try {
            $validated = $shipBubble->validateAddress([
                'name' => trim("{$this->first_name} {$this->last_name}"),
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => collect([$this->street_address, $this->city, $this->state, $this->country])->filter()->implode(', '),
            ]);

            $deliveryAddressCode = ($validated['data'] ?? $validated)['address_code'] ?? null;

            if (! $deliveryAddressCode) {
                $this->shippingError = "We couldn't verify that delivery address. Please check it and try again.";

                return;
            }

            $cart = $cartService->current()->load('items.product');

            $packageItems = $cart->items->map(fn ($item) => [
                'name' => $item->product->name,
                'description' => $item->product->name,
                'unit_weight' => (string) ($item->product->package_dimension['max_weight'] ?? 1),
                'unit_amount' => (string) $item->unit_price,
                'quantity' => (string) $item->quantity,
            ])->values()->all();

            if (empty($packageItems)) {
                $this->shippingError = 'Your bag is empty.';

                return;
            }

            $dimension = $cart->items
                ->pluck('product.package_dimension')
                ->filter()
                ->sortByDesc(fn ($dim) => ($dim['length'] ?? 0) * ($dim['width'] ?? 0) * ($dim['height'] ?? 0))
                ->first() ?? ['length' => 35, 'width' => 28, 'height' => 15];

            // package_category_id must be a real ShipBubble category id (large
            // account-agnostic numbers, not 1/2/3) - fall back to a real id
            // resolved from ShipBubble's own category list rather than
            // guessing, since an invalid id gets the whole request rejected.
            $categoryId = $cart->items->pluck('product.package_category_id')->filter()->first()
                ?? $shipBubble->resolveDefaultCategoryId();

            if (! $categoryId) {
                $this->shippingError = 'Shipping is not available right now - no package category is configured. Please contact support.';

                return;
            }

            $rates = $shipBubble->getRates([
                'sender_address_code' => (int) $pickup->address_code,
                'reciever_address_code' => (int) $deliveryAddressCode,
                'pickup_date' => now()->addDay()->toDateString(),
                'category_id' => (int) $categoryId,
                'package_items' => $packageItems,
                'package_dimension' => [
                    'length' => (int) ($dimension['length'] ?? 35),
                    'width' => (int) ($dimension['width'] ?? 28),
                    'height' => (int) ($dimension['height'] ?? 15),
                ],
            ]);

            $couriers = $rates['data']['couriers'] ?? [];

            if (empty($couriers)) {
                $this->shippingError = 'No couriers are available for this address right now.';

                return;
            }

            $this->shippingRequestToken = $rates['data']['request_token'] ?? null;

            // ShipBubble always quotes in NGN - convert each rate into the cart's
            // active currency so what the customer picks matches what they've
            // been shopping in.
            $cartCurrency = $cart->currency;

            $this->liveCouriers = collect($couriers)
                ->map(function ($courier) use ($currencyService, $cartCurrency) {
                    $amountNgn = (float) ($courier['rate_card_amount'] ?? $courier['total'] ?? 0);

                    $courier['display_amount'] = $currencyService->convert($amountNgn, 'NGN', $cartCurrency);
                    $courier['display_currency'] = $cartCurrency;

                    return $courier;
                })
                ->values()
                ->all();

            // Default to the cheapest live rate so the customer sees it pre-selected.
            $cheapestIndex = collect($this->liveCouriers)
                ->keys()
                ->sortBy(fn ($index) => $this->liveCouriers[$index]['display_amount'] ?? PHP_FLOAT_MAX)
                ->first();

            $this->selectedCourierIndex = $cheapestIndex;
        } catch (\Throwable $e) {
            Log::warning('ShipBubble checkout rate fetch failed: '.$e->getMessage());
            $this->shippingError = 'Live courier rates are unavailable right now. Please try again in a moment.';
        }
    }

    /**
     * Resolve the selected courier into a cost + a ShippingMethod instance,
     * built in-memory (never persisted) purely to carry cost/name/currency
     * through OrderService, which expects a ShippingMethod.
     */
    protected function resolveSelectedShipping(): ?array
    {
        $courier = $this->selectedCourierIndex !== null ? ($this->liveCouriers[$this->selectedCourierIndex] ?? null) : null;

        if (! $courier) {
            return null;
        }

        $cost = (float) ($courier['display_amount'] ?? 0);

        $shippingMethod = new ShippingMethod([
            'name' => $courier['courier_name'] ?? 'Courier',
            'code' => trim(($courier['courier_name'] ?? 'Courier').' ('.($courier['service_type'] ?? 'dropoff').')'),
            'cost' => $cost,
            'currency' => $courier['display_currency'] ?? 'NGN',
        ]);

        return [
            'cost' => $cost,
            'shippingMethod' => $shippingMethod,
            'requestToken' => $this->shippingRequestToken,
            'serviceCode' => $courier['service_code'] ?? null,
            'courierId' => $courier['courier_id'] ?? null,
        ];
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;
        }
    }

    public function applyDiscount(CartService $cartService): void
    {
        $this->discountError = null;
        $code = DiscountCode::where('code', strtoupper($this->discount_code))->first();

        $cart = $cartService->current();

        if (! $code || ! $code->isValidFor($cart->subtotal)) {
            $this->discountError = 'This code is not valid for your order.';

            return;
        }
    }

    public function placeOrder(CartService $cartService, OrderService $orderService, PaymentService $paymentService, ShipBubbleService $shipBubble): void
    {
        $this->validate(array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
            $this->rulesForStep(3),
            $this->rulesForStep(4),
        ));

        $cart = $cartService->current();

        if ($cart->items()->count() === 0) {
            $this->redirect(route('cart.index'), navigate: false);

            return;
        }

        $resolvedShipping = $this->resolveSelectedShipping();

        if (! $resolvedShipping) {
            $this->addError('selectedCourierIndex', 'Please select a shipping option.');
            $this->step = 3;

            return;
        }

        $shippingMethod = $resolvedShipping['shippingMethod'];
        $discountCode = $this->discount_code
            ? DiscountCode::where('code', strtoupper($this->discount_code))->first()
            : null;

        try {
            $order = $orderService->createFromCart(
                $cart,
                [
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'street_address' => $this->street_address,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip_code' => $this->zip_code,
                    'country' => $this->country,
                ],
                $shippingMethod,
                $discountCode,
                $this->payment_method,
                [
                    'notes' => $this->notes,
                    'shipbubble_request_token' => $resolvedShipping['requestToken'],
                    'shipbubble_service_code' => $resolvedShipping['serviceCode'],
                    'shipbubble_courier_id' => $resolvedShipping['courierId'],
                ]
            );
        } catch (\RuntimeException $e) {
            $this->addError('stock', $e->getMessage());

            return;
        }

        if ($this->payment_method === 'cod') {
            $order->update(['status' => 'confirmed']);
            session(['success_order_id' => $order->id]);

            $orderService->bookShipment($order, $shipBubble);

            try {
                Mail::to($this->email)->send(new OrderPlaced($order));
            } catch (\Throwable $e) {
                report($e);
            }

            $this->redirect(route('checkout.success'), navigate: false);

            return;
        }

        $result = $paymentService->initializeForOrder($order, route('paystack.callback'), $this->email);

        $this->redirect($result['authorization_url'], navigate: false);
    }

    public function render(CartService $cartService, CurrencyService $currencyService)
    {
        $cart = $cartService->current()->load('items.product', 'items.variant.color', 'items.variant.size');
        $activeCurrency = $currencyService->getCurrentCurrency();

        $resolvedShipping = $this->resolveSelectedShipping();
        $shippingCost = $resolvedShipping['cost'] ?? 0;
        $discountCode = $this->discount_code ? DiscountCode::where('code', strtoupper($this->discount_code))->first() : null;
        $discountAmount = ($discountCode && $discountCode->isValidFor($cart->subtotal)) ? $discountCode->calculateDiscount($cart->subtotal) : 0;
        $grandTotal = $cart->subtotal - $discountAmount + $shippingCost;

        return view('livewire.checkout.checkout-flow', [
            'cart' => $cart,
            'activeCurrency' => $activeCurrency,
            'liveCouriers' => $this->liveCouriers,
            'shippingCost' => $shippingCost,
            'discountAmount' => $discountAmount,
            'grandTotal' => $grandTotal,
        ]);
    }
}
