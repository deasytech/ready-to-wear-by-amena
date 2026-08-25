<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ShipBubbleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShipBubbleWebhookController extends Controller
{
    /**
     * Maps ShipBubble's documented shipment statuses (pending, confirmed,
     * picked_up, in_transit, completed, cancelled) onto our order lifecycle.
     */
    protected const STATUS_MAP = [
        'pending' => 'processing',
        'confirmed' => 'processing',
        'picked_up' => 'shipped',
        'in_transit' => 'shipped',
        'completed' => 'delivered',
        'cancelled' => 'cancelled',
    ];

    /**
     * ShipBubble requires a 200 response within 15 seconds or the event is
     * marked failed and retried, so everything after signature verification
     * is best-effort: any failure is logged, never surfaced as an error
     * response, so a problem on our side doesn't trigger a retry storm.
     */
    public function handle(Request $request, ShipBubbleService $shipBubble)
    {
        $signature = $request->header('x-ship-signature');

        if (! $shipBubble->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('ShipBubble webhook signature verification failed.');

            return response()->noContent(401);
        }

        try {
            $shipmentId = $request->input('order_id');
            $status = strtolower((string) $request->input('status'));

            if (! $shipmentId) {
                return response()->noContent();
            }

            $order = Order::where('shipment_id', $shipmentId)->first();

            if (! $order) {
                Log::warning("ShipBubble webhook for unknown shipment {$shipmentId}");

                return response()->noContent();
            }

            $mapped = self::STATUS_MAP[$status] ?? null;

            if ($mapped) {
                if ($mapped !== $order->status) {
                    $order->update(['status' => $mapped]);
                }
            } else {
                Log::info("ShipBubble webhook: unmapped status '{$status}' for order {$order->id}");
            }
        } catch (\Throwable $e) {
            Log::error('ShipBubble webhook processing failed: '.$e->getMessage());
        }

        return response()->noContent();
    }
}
