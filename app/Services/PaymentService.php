<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Currencies Paystack's API accepts at all. A merchant account still needs
     * each one explicitly enabled on their dashboard (NGN is always on; USD
     * can be added alongside it for NG/KE businesses; GHS/ZAR/KES otherwise
     * need a separate business account registered in that country) - we can't
     * know that from here, so initializeForOrder() falls back to NGN if the
     * attempt is rejected.
     */
    protected const PAYSTACK_CURRENCIES = ['NGN', 'USD', 'GHS', 'ZAR', 'KES'];

    public function __construct(protected PaymentGatewayInterface $gateway, protected CurrencyService $currency) {}

    /**
     * Start a payment for an order and return the redirect URL + reference.
     * Charges in the order's currency when Paystack can accept it, otherwise
     * converts to NGN - every Paystack account can charge NGN.
     */
    public function initializeForOrder(Order $order, string $callbackUrl, string $email): array
    {
        $reference = 'RTW-'.strtoupper(Str::random(10));
        $orderCurrency = $order->currency ?? 'NGN';

        $chargeCurrency = in_array($orderCurrency, self::PAYSTACK_CURRENCIES, true) ? $orderCurrency : 'NGN';
        $chargeAmount = $chargeCurrency === $orderCurrency
            ? (float) $order->grand_total
            : $this->currency->convert((float) $order->grand_total, $orderCurrency, $chargeCurrency);

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => 'paystack',
            'reference' => $reference,
            'amount' => $chargeAmount,
            'currency' => $chargeCurrency,
            'status' => 'pending',
        ]);

        $order->update(['payment_reference' => $reference]);

        try {
            $result = $this->gateway->initialize([
                'email' => $email,
                'amount' => $chargeAmount,
                'currency' => $chargeCurrency,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
            ]);
        } catch (\Throwable $e) {
            if ($chargeCurrency === 'NGN') {
                throw $e;
            }

            Log::warning("Paystack rejected currency {$chargeCurrency} for order {$order->id}, retrying in NGN: {$e->getMessage()}");

            $chargeCurrency = 'NGN';
            $chargeAmount = $this->currency->convert((float) $order->grand_total, $orderCurrency, 'NGN');
            $payment->update(['amount' => $chargeAmount, 'currency' => $chargeCurrency]);

            $result = $this->gateway->initialize([
                'email' => $email,
                'amount' => $chargeAmount,
                'currency' => $chargeCurrency,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
            ]);
        }

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

        // The Paystack webhook may have already confirmed this reference (it fires
        // independently of the browser callback), so short-circuit before hitting
        // the gateway again - that also avoids a spurious "reference not found"
        // if Paystack hasn't finished propagating the transaction yet.
        if ($payment->status === 'paid') {
            return $payment;
        }

        try {
            $result = $this->gateway->verify($reference);
        } catch (\Throwable $e) {
            Log::error("Failed to verify payment {$reference}: {$e->getMessage()}");

            return $payment->fresh();
        }

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
