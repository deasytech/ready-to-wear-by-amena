<?php

namespace App\Services;

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
     * Package categories used to describe what is being shipped.
     * Falls back to a static fashion-relevant list if the API is unreachable,
     * so the admin Product form never hard-crashes in local/dev environments.
     */
    public function getPackageCategories(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/shipping/labels/categories");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning('ShipBubble getPackageCategories failed: '.$e->getMessage());
        }

        return [
            'data' => [
                ['category_id' => 1, 'category' => 'Fashion & Clothing'],
                ['category_id' => 2, 'category' => 'Shoes & Accessories'],
                ['category_id' => 3, 'category' => 'General Merchandise'],
            ],
        ];
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
        return $this->client()
            ->post("{$this->baseUrl}/shipping/address/validate", $payload)
            ->throw()
            ->json();
    }

    /**
     * Update a previously validated address.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function updateAddress(array $payload): array
    {
        return $this->client()
            ->put("{$this->baseUrl}/shipping/address/update", $payload)
            ->throw()
            ->json();
    }

    /**
     * Fetch live courier rates for a shipment. Returns an empty rate list
     * (rather than throwing) so checkout can fall back to the flat-rate
     * shipping methods configured in the admin.
     */
    public function getRates(array $payload): array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/shipping/labels/rates", $payload);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            Log::warning('ShipBubble getRates failed: '.$e->getMessage());
        }

        return ['data' => ['couriers' => []]];
    }

    protected function client()
    {
        return Http::withToken($this->apiKey)->acceptJson()->timeout(10);
    }
}
