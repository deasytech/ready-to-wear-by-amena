<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(protected PaymentGatewayInterface $gateway) {}

    /**
     * Start a payment for an order and return the redirect URL + reference.
     */
    public function initializeForOrder(Order $order, string $callbackUrl, string $email): array
    {
        $reference = 'RTW-'.strtoupper(Str::random(10));

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'paystack',
            'reference' => $reference,
            'amount' => $order->grand_total,
            'currency' => $order->currency ?? 'NGN',
            'status' => 'pending',
        ]);

        $order->update(['payment_reference' => $reference]);

        $result = $this->gateway->initialize([
            'email' => $email,
            'amount' => $order->grand_total,
            'currency' => $order->currency ?? 'NGN',
            'reference' => $reference,
            'callback_url' => $callbackUrl,
        ]);

        return [
            'payment' => $payment,
            'authorization_url' => $result['authorization_url'],
        ];
    }

    /**
     * Verify a transaction with the gateway and sync the local Payment + Order.
     */
    public function completePayment(string $reference): ?Payment
    {
        $payment = Payment::where('reference', $reference)->first();

        if (! $payment) {
            return null;
        }

        $result = $this->gateway->verify($reference);

        $payment->update([
            'status' => $result['successful'] ? 'paid' : 'failed',
            'paid_at' => $result['successful'] ? now() : null,
            'raw_response' => $result['raw'],
        ]);

        $order = $payment->order;

        if ($result['successful']) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);
        } else {
            $order->update(['payment_status' => 'failed']);
        }

        return $payment->fresh();
    }
}
