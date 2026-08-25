<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaystackController extends Controller
{
    public function handleCallback(Request $request, PaymentService $paymentService)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect()->route('checkout.cancel')->with('error', 'No payment reference provided.');
        }

        $wasAlreadyPaid = Payment::where('reference', $reference)->value('status') === 'paid';
        $payment = $paymentService->completePayment($reference);

        if (! $payment) {
            return redirect()->route('checkout.cancel')->with('error', 'Order not found for this transaction.');
        }

        if ($payment->status !== 'paid') {
            return redirect()->route('checkout.cancel')->with('error', 'Payment was not successful.');
        }

        if (! $wasAlreadyPaid) {
            $this->sendConfirmation($payment);
        }

        session(['success_order_id' => $payment->order_id]);

        return redirect()->route('checkout.success')->with('success', 'Payment successful!');
    }

    /**
     * Paystack server-to-server webhook. Verified independently of the browser
     * callback so a payment is still confirmed if the customer closes the tab
     * before the redirect completes.
     */
    public function handleWebhook(Request $request, PaymentService $paymentService)
    {
        $signature = $request->header('x-paystack-signature');
        $secret = config('services.paystack.secret_key');

        if (! $signature || ! $secret || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            Log::warning('Paystack webhook signature verification failed.');

            return response()->noContent(401);
        }

        $reference = $request->input('data.reference');

        if ($reference && $request->input('event') === 'charge.success') {
            $wasAlreadyPaid = Payment::where('reference', $reference)->value('status') === 'paid';
            $payment = $paymentService->completePayment($reference);

            if ($payment && $payment->status === 'paid' && ! $wasAlreadyPaid) {
                $this->sendConfirmation($payment);
            }
        }

        return response()->noContent();
    }

    protected function sendConfirmation(Payment $payment): void
    {
        $order = $payment->order()->with('items', 'address', 'user')->first();

        $recipient = $order->address->email ?? $order->user?->email;

        if (! $recipient) {
            Log::warning("Order {$order->id} has no address email or user - skipping order confirmation email.");

            return;
        }

        // Mail delivery failures (SMTP outages, cert issues, etc.) must never
        // fail the checkout request - the payment is already confirmed at this
        // point, so a mail error here should just be logged, not surfaced as a
        // 500 to the customer.
        try {
            Mail::to($recipient)->send(new OrderPlaced($order));
        } catch (\Throwable $e) {
            Log::error("Failed to send order confirmation email for order {$order->id}: {$e->getMessage()}");
        }
    }
}
