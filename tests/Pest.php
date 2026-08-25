<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Fake ShipBubble's address-validation and rate-fetching endpoints, and seed
 * a pickup CompanyAddress, so checkout tests can reach step 3 without hitting
 * the real ShipBubble API.
 */
function fakeShipBubble(): void
{
    \App\Models\CompanyAddress::create([
        'name' => 'RTW Warehouse',
        'email' => 'warehouse@example.com',
        'phone' => '+2348000000000',
        'address' => '1 Warehouse Road, Lagos',
        'address_code' => 111,
    ]);

    \Illuminate\Support\Facades\Http::fake([
        '*/shipping/labels/categories' => \Illuminate\Support\Facades\Http::response([
            'status' => 'success',
            'data' => [
                ['category_id' => 74794423, 'category' => 'Fashion wears'],
            ],
        ], 200),
        '*/shipping/address/validate' => \Illuminate\Support\Facades\Http::response([
            'status' => true,
            'message' => 'Address validated',
            'data' => [
                'address_code' => 222,
                'formatted_address' => '10 Admiralty Way, Lekki, Lagos',
                'city' => 'Lekki',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'postal_code' => '101245',
                'latitude' => 6.4,
                'longitude' => 3.4,
            ],
        ], 200),
        '*/shipping/fetch_rates' => \Illuminate\Support\Facades\Http::response([
            'status' => 'success',
            'message' => 'Retrieved successfully',
            'data' => [
                'request_token' => 'fake-request-token',
                'couriers' => [
                    [
                        'courier_id' => 1,
                        'courier_name' => 'Fake Courier',
                        'rate_card_amount' => 2500,
                        'total' => 2500,
                        'currency' => 'NGN',
                        'service_code' => 'fake-standard',
                        'service_type' => 'pickup',
                        'delivery_eta_time' => '2 days',
                    ],
                ],
            ],
        ], 200),
        '*/shipping/labels' => \Illuminate\Support\Facades\Http::response([
            'status' => 'success',
            'message' => 'Order successfully routed to Fake Courier',
            'data' => [
                'order_id' => 'SB-FAKE12345',
                'status' => 'pending',
                'tracking_url' => 'https://shipbubble.test/tracking/SB-FAKE12345',
            ],
        ], 200),
    ]);
}
