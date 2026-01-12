<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ChargilyPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChargilyWebhookController extends Controller
{
    protected ChargilyPayService $chargilyService;

    public function __construct(ChargilyPayService $chargilyService)
    {
        $this->chargilyService = $chargilyService;
    }

    /**
     * Handle Chargily webhook events.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('signature');

        // Verify webhook signature
        if (!$signature || !$this->chargilyService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Chargily webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->all();
        $eventType = $data['type'] ?? null;

        Log::info('Chargily webhook received', [
            'type' => $eventType,
            'data' => $data,
        ]);

        switch ($eventType) {
            case 'checkout.paid':
                $this->handleCheckoutPaid($data);
                break;

            case 'checkout.failed':
                $this->handleCheckoutFailed($data);
                break;

            default:
                Log::info('Unhandled Chargily webhook event', ['type' => $eventType]);
        }

        return response()->json(['status' => 'received']);
    }

    /**
     * Handle successful payment.
     */
    protected function handleCheckoutPaid(array $data): void
    {
        $checkoutData = $data['data'] ?? [];
        $metadata = $checkoutData['metadata'] ?? [];
        $orderId = $metadata['order_id'] ?? null;

        if (!$orderId) {
            Log::error('Chargily webhook: No order_id in metadata', $data);
            return;
        }

        $order = Order::find($orderId);

        if (!$order) {
            Log::error('Chargily webhook: Order not found', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'chargily_checkout_id' => $checkoutData['id'] ?? null,
        ]);

        Log::info('Order payment confirmed via Chargily', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }

    /**
     * Handle failed payment.
     */
    protected function handleCheckoutFailed(array $data): void
    {
        $checkoutData = $data['data'] ?? [];
        $metadata = $checkoutData['metadata'] ?? [];
        $orderId = $metadata['order_id'] ?? null;

        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);

        if ($order) {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
            ]);

            Log::info('Order payment failed via Chargily', [
                'order_id' => $order->id,
            ]);
        }
    }
}
