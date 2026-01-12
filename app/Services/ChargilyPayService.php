<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChargilyPayService
{
    protected ?string $apiUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.chargily.api_url');
        $this->apiKey = config('services.chargily.api_key');
    }

    /**
     * Create a checkout session for an order.
     */
    public function createCheckout(Order $order): array
    {
        $http = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ]);

        // Disable SSL verification in local/development
        if (config('app.env') !== 'production') {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($this->apiUrl . '/checkouts', [
            'amount' => (int) $order->total, // Amount in dinars
            'currency' => 'dzd',
            'success_url' => config('services.chargily.success_url') . '?order=' . $order->order_number,
            'failure_url' => config('services.chargily.failure_url'),
            'webhook_endpoint' => config('app.url') . '/api/v1/webhooks/chargily',
            'description' => 'Order #' . $order->order_number,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        if ($response->failed()) {
            Log::error('Chargily checkout creation failed', [
                'order_id' => $order->id,
                'response' => $response->json(),
            ]);

            throw new \Exception('Failed to create payment checkout: ' . ($response->json('message') ?? 'Unknown error'));
        }

        $data = $response->json();

        return [
            'checkout_id' => $data['id'],
            'checkout_url' => $data['checkout_url'],
        ];
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $computedSignature = hash_hmac('sha256', $payload, $this->apiKey);
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Get checkout status.
     */
    public function getCheckout(string $checkoutId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->get($this->apiUrl . '/checkouts/' . $checkoutId);

        if ($response->failed()) {
            throw new \Exception('Failed to get checkout status');
        }

        return $response->json();
    }
}
