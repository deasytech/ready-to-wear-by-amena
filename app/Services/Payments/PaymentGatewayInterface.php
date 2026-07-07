<?php

namespace App\Services\Payments;

interface PaymentGatewayInterface
{
    /**
     * Initialize a transaction and return at least an 'authorization_url'.
     */
    public function initialize(array $data): array;

    /**
     * Verify a transaction by its reference and return a normalised result:
     * ['successful' => bool, 'amount' => float (major units), 'currency' => string, 'raw' => array].
     */
    public function verify(string $reference): array;
}
