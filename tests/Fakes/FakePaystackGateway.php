<?php

namespace Tests\Fakes;

use App\Services\Payments\PaymentGatewayInterface;

class FakePaystackGateway implements PaymentGatewayInterface
{
    public static bool $shouldSucceed = true;

    public function initialize(array $data): array
    {
        return [
            'authorization_url' => 'https://checkout.paystack.com/fake-'.$data['reference'],
            'access_code' => 'fake-access-code',
            'reference' => $data['reference'],
            'raw' => [],
        ];
    }

    public function verify(string $reference): array
    {
        return [
            'successful' => self::$shouldSucceed,
            'amount' => 0,
            'currency' => 'NGN',
            'raw' => ['reference' => $reference],
        ];
    }
}
