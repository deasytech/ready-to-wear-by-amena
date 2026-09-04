<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShipBubbleService
{
    protected string $baseUrl;

    protected ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.shipbubble.base_url'), '/');
        $this->apiKey = config('services.shipbubble.api_key');
    }

    /**
     * Package categories used to describe what is being shipped. ShipBubble
     * assigns each category a large, account-agnostic numeric id (e.g.
     * "Fashion wears" = 74794423) - these are NOT small sequential ids, and
     * fetch_rates rejects anything else with a 422 "Invalid package category
     * selected". Cached for a while since this list rarely changes, and only
     * the successful response is cached - a transient failure always retries
     * fresh next time rather than getting stuck on the fallback.
     */
    public function getPackageCategories(): array
    {
        $cached = Cache::get('shipbubble.package_categories');

        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/shipping/labels/categories");

            if ($response->successful()) {
                $data = $response->json();
                Cache::put('shipbubble.package_categories', $data, now()->addHours(6));

                return $data;
            }
        } catch (\Throwable $e) {
            Log::warning('ShipBubble getPackageCategories failed: '.$e->getMessage());
        }

        // Real category ids from ShipBubble's catalogue, used only when the
        // categories endpoint itself is unreachable. These must stay real
        // ids (not placeholders) since an invented id breaks fetch_rates.
        return [
            'data' => [
                ['category_id' => 74794423, 'category' => 'Fashion wears'],
                ['category_id' => 99652979, 'category' => 'Health and beauty'],
                ['category_id' => 20754594, 'category' => 'Light weight items'],
            ],
        ];
    }

    /**
     * A real category_id to use when a product hasn't been assigned one.
     * Prefers a fashion-related category (this store's default), falling
     * back to whatever category comes back first - never invents an id.
     */
    public function resolveDefaultCategoryId(): ?int
    {
        $categories = collect($this->getPackageCategories()['data'] ?? []);

        $category = $categories->first(fn ($c) => str_contains(strtolower($c['category'] ?? ''), 'fashion'))
            ?? $categories->first();

        return isset($category['category_id']) ? (int) $category['category_id'] : null;
    }

    /**
     * Standard box/package dimensions offered by the courier network.
     * Falls back to fashion-appropriate parcel presets on API failure.
     */
    public function getPackageDimensions(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/shipping/labels/boxes");

            if ($response->successful()) {
                $data = $response->json('data') ?? $response->json();

                if (! empty($data)) {
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ShipBubble getPackageDimensions failed: '.$e->getMessage());
        }

        return [
            ['name' => 'Small Parcel', 'length' => 25, 'width' => 20, 'height' => 10, 'max_weight' => 2, 'description_image_url' => null],
            ['name' => 'Medium Parcel', 'length' => 35, 'width' => 28, 'height' => 15, 'max_weight' => 5, 'description_image_url' => null],
            ['name' => 'Large Parcel', 'length' => 45, 'width' => 35, 'height' => 25, 'max_weight' => 10, 'description_image_url' => null],
        ];
    }

    /**
     * Validate/normalise a pickup or delivery address with the courier network.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function validateAddress(array $payload): array
    {
        $url = "{$this->baseUrl}/shipping/address/validate";

        try {
            return $this->client()->post($url, $payload)->throw()->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->logRequestFailure('POST', $url, $payload, $e);

            throw $e;
        }
    }

    /**
     * Update a previously validated address.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function updateAddress(array $payload): array
    {
        $url = "{$this->baseUrl}/shipping/address/update";

        try {
            return $this->client()->put($url, $payload)->throw()->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $this->logRequestFailure('PUT', $url, $payload, $e);

            throw $e;
        }
    }

    /**
     * Logs everything needed to diagnose a failed ShipBubble call after the
     * fact (method, url, payload, status, full response body/headers) -
     * added because production (cPanel, no SSH/tinker access) kept returning
     * 404 "Requested resource not available" on address validate/update
     * while the same payload succeeded locally, and the bare exception
     * message alone wasn't enough to tell create vs. update, or which
     * address/address_code was involved.
     */
    protected function logRequestFailure(string $method, string $url, array $payload, \Illuminate\Http\Client\RequestException $e): void
    {
        Log::error("ShipBubble {$method} {$url} failed", [
            'payload' => $payload,
            'status' => $e->response->status(),
            'body' => $e->response->json() ?? $e->response->body(),
            'rate_limit_headers' => collect($e->response->headers())
                ->filter(fn ($v, $k) => str_contains(strtolower($k), 'rate') || str_contains(strtolower($k), 'limit'))
                ->all(),
        ]);
    }

    /**
     * Fetch live courier rates for a shipment. Returns an empty rate list
     * (rather than throwing) so the caller can show its own "no rates
     * available" state instead of a hard failure.
     */
    public function getRates(array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/shipping/fetch_rates", $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ShipBubble getRates returned a non-successful response', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ShipBubble getRates failed: '.$e->getMessage());
        }

        return ['data' => ['couriers' => []]];
    }

    /**
     * Book an actual shipment against a previously fetched rate. The
     * request_token comes from getRates() and is single-use, tied to that
     * specific rate quote - service_code/courier_id identify which of the
     * quoted couriers was picked.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createShipment(array $payload): array
    {
        return $this->client()
            ->post("{$this->baseUrl}/shipping/labels", $payload)
            ->throw()
            ->json();
    }

    /**
     * Verify the x-ship-signature header ShipBubble sends with webhook
     * requests: an HMAC-SHA512 hash of the raw request body, signed with the
     * same API key used for authenticating requests to them.
     */
    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $signature || ! $this->apiKey) {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $this->apiKey), $signature);
    }

    /**
     * A transient network blip (timeout, connection reset) against an
     * external API shouldn't fail an admin action outright, so connection-
     * level failures and 5xx responses get a couple of quick retries. A 4xx
     * means the payload itself was rejected, so retrying it is pointless -
     * only the network/server error classes are retried.
     */
    protected function client()
    {
        return Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 500, function (\Throwable $e) {
                return $e instanceof \Illuminate\Http\Client\ConnectionException
                    || ($e instanceof \Illuminate\Http\Client\RequestException && $e->response->serverError());
            }, throw: false);
    }
}
