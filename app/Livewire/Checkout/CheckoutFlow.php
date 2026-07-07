<?php

namespace App\Livewire\Checkout;

use App\Mail\OrderPlaced;
use App\Models\DiscountCode;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CurrencyService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
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

    // Step 3: delivery method
    public ?int $shipping_method_id = null;

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

        $this->shipping_method_id = ShippingMethod::active()->orderBy('sort_order')->first()?->id;
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
            3 => ['shipping_method_id' => 'required|exists:shipping_methods,id'],
            4 => ['payment_method' => 'required|in:paystack,cod'],
            default => [],
        };
    }

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step));
        $this->step = min(5, $this->step + 1);
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

    public function placeOrder(CartService $cartService, OrderService $orderService, PaymentService $paymentService): void
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

        $shippingMethod = ShippingMethod::findOrFail($this->shipping_method_id);
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
                ['notes' => $this->notes]
            );
        } catch (\RuntimeException $e) {
            $this->addError('stock', $e->getMessage());

            return;
        }

        if ($this->payment_method === 'cod') {
            $order->update(['status' => 'confirmed']);
            session(['success_order_id' => $order->id]);
            Mail::to($this->email)->send(new OrderPlaced($order));

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

        $shippingMethod = $this->shipping_method_id ? ShippingMethod::find($this->shipping_method_id) : null;
        $discountCode = $this->discount_code ? DiscountCode::where('code', strtoupper($this->discount_code))->first() : null;
        $discountAmount = ($discountCode && $discountCode->isValidFor($cart->subtotal)) ? $discountCode->calculateDiscount($cart->subtotal) : 0;
        $grandTotal = $cart->subtotal - $discountAmount + ($shippingMethod?->cost ?? 0);

        return view('livewire.checkout.checkout-flow', [
            'cart' => $cart,
            'activeCurrency' => $activeCurrency,
            'shippingMethods' => ShippingMethod::active()->orderBy('sort_order')->get(),
            'shippingCost' => $shippingMethod?->cost ?? 0,
            'discountAmount' => $discountAmount,
            'grandTotal' => $grandTotal,
        ]);
    }
}
