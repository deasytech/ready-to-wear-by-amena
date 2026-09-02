<?php

use App\Livewire\Checkout\CheckoutFlow;
use App\Livewire\Storefront\Header;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Fakes\FakePaystackGateway;

beforeEach(function () {
    $this->app->bind(PaymentGatewayInterface::class, FakePaystackGateway::class);
    FakePaystackGateway::$shouldSucceed = true;
});

it('converts shipbubble NGN rates into the cart currency', function () {
    fakeShipBubble();

    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['price' => 50000, 'price_usd' => 33]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    app(CartService::class)->addItem($product, $variant, 1);
    app(CartService::class)->syncCurrency(app(CartService::class)->current(), 'USD');

    $component = Livewire::test(CheckoutFlow::class)
        ->set('first_name', 'Amaka')
        ->set('last_name', 'Okafor')
        ->set('email', 'amaka@example.com')
        ->set('phone', '+2348012345678')
        ->call('nextStep')
        ->set('street_address', '10 Admiralty Way')
        ->set('city', 'Lekki')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->call('nextStep');

    $couriers = $component->get('liveCouriers');

    expect($couriers)->toHaveCount(1);
    expect($couriers[0]['display_currency'])->toBe('USD');
    // 2500 NGN * 0.0013 (config exchange rate) rounded to 2dp.
    expect((float) $couriers[0]['display_amount'])->toBe(round(2500 * 0.0013, 2));
});

it('shows a retryable error and blocks progress when shipbubble has no pickup address configured', function () {
    Http::fake();

    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['price' => 50000]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    app(CartService::class)->addItem($product, $variant, 1);

    $component = Livewire::test(CheckoutFlow::class)
        ->set('first_name', 'Amaka')
        ->set('last_name', 'Okafor')
        ->set('email', 'amaka@example.com')
        ->set('phone', '+2348012345678')
        ->call('nextStep')
        ->set('street_address', '10 Admiralty Way')
        ->set('city', 'Lekki')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->call('nextStep');

    expect($component->get('liveCouriers'))->toBeEmpty();
    expect($component->get('shippingError'))->not->toBeNull();

    $component->call('nextStep')->assertHasErrors('selectedCourierIndex');

    expect(Order::count())->toBe(0);
});

it('falls back to NGN when paystack rejects the order currency', function () {
    fakeShipBubble();

    Http::fake([
        '*/shipping/address/validate' => Http::response(['data' => ['address_code' => 222]], 200),
        '*/shipping/fetch_rates' => Http::response([
            'data' => ['couriers' => [
                ['courier_id' => 1, 'courier_name' => 'Fake Courier', 'rate_card_amount' => 2500, 'currency' => 'NGN'],
            ]],
        ], 200),
    ]);

    // Simulate Paystack rejecting the USD attempt (e.g. currency not enabled
    // on the account) by binding a gateway that throws once, then succeeds.
    $gateway = new class implements PaymentGatewayInterface
    {
        public static array $seenCurrencies = [];

        public function initialize(array $data): array
        {
            self::$seenCurrencies[] = $data['currency'];

            if ($data['currency'] !== 'NGN') {
                Http::response(['message' => 'Currency not supported'], 400)->throw();
            }

            return [
                'authorization_url' => 'https://checkout.paystack.com/fake-'.$data['reference'],
                'access_code' => 'fake',
                'reference' => $data['reference'],
                'raw' => [],
            ];
        }

        public function verify(string $reference): array
        {
            return ['successful' => true, 'amount' => 0, 'currency' => 'NGN', 'raw' => []];
        }
    };

    $this->app->instance(PaymentGatewayInterface::class, $gateway);

    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['price' => 50000, 'price_usd' => 33]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    app(CartService::class)->addItem($product, $variant, 1);
    app(CartService::class)->syncCurrency(app(CartService::class)->current(), 'USD');

    Livewire::test(CheckoutFlow::class)
        ->set('first_name', 'Amaka')
        ->set('last_name', 'Okafor')
        ->set('email', 'amaka@example.com')
        ->set('phone', '+2348012345678')
        ->call('nextStep')
        ->set('street_address', '10 Admiralty Way')
        ->set('city', 'Lekki')
        ->set('state', 'Lagos')
        ->set('country', 'Nigeria')
        ->call('nextStep')
        ->call('nextStep')
        ->set('payment_method', 'paystack')
        ->call('nextStep')
        ->call('placeOrder');

    $order = Order::first();

    expect($order->payment->currency)->toBe('NGN');
    expect($gateway::$seenCurrencies)->toBe(['USD', 'NGN']);
});

it('lets a customer switch currency from the header', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = Product::factory()->create(['price' => 50000, 'price_usd' => 33]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5, 'price_override' => 40000, 'price_override_usd' => 26]);

    $cartService = app(CartService::class);
    $cartService->addItem($product, $variant, 1);

    expect((float) $cartService->current()->items->first()->unit_price)->toBe(40000.0);

    $component = Livewire::test(Header::class)->call('changeCurrency', 'USD');

    expect($cartService->current()->fresh()->currency)->toBe('USD');
    expect((float) $cartService->current()->fresh()->items->first()->unit_price)->toBe(26.0);

    // Regression: redirecting to url()->current() inside a Livewire action
    // resolves to the /livewire/update AJAX endpoint the browser just posted
    // to, not the page the component is on - redirecting there breaks with a
    // 405 since that route only accepts POST.
    expect($component->effects['redirect'] ?? null)->not->toContain('livewire/update');
});
