<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class PaystackGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;

    protected ?string $secretKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.paystack.base_url'), '/');
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function initialize(array $data): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email' => $data['email'],
                'amount' => (int) round($data['amount'] * 100), // kobo
                'reference' => $data['reference'],
                'currency' => $data['currency'] ?? 'NGN',
                'callback_url' => $data['callback_url'] ?? null,
            ])
            ->throw()
            ->json();

        return [
            'authorization_url' => $response['data']['authorization_url'] ?? null,
            'access_code' => $response['data']['access_code'] ?? null,
            'reference' => $response['data']['reference'] ?? $data['reference'],
            'raw' => $response,
        ];
    }

    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->acceptJson()
            ->get("{$this->baseUrl}/transaction/verify/{$reference}")
            ->throw()
            ->json();

        $status = $response['data']['status'] ?? null;

        return [
            'successful' => $status === 'success',
            'amount' => isset($response['data']['amount']) ? $response['data']['amount'] / 100 : 0,
            'currency' => $response['data']['currency'] ?? 'NGN',
            'raw' => $response,
        ];
    }
}
