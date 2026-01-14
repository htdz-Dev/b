<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ChargilyPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected ChargilyPayService $chargilyService;

    public function __construct(ChargilyPayService $chargilyService)
    {
        $this->chargilyService = $chargilyService;
    }

    /**
     * Get user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product.images'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Get a specific order.
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->with(['items.product', 'items.variant'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return response()->json([
            'data' => $order,
        ]);
    }

    /**
     * Create a new order (Checkout).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_wilaya' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod,chargily',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $subtotal = 0;
            $orderItems = [];

            // Validate stock and calculate totals
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $variant = null;
                $unitPrice = (float) $product->price;

                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::where('id', $item['variant_id'])
                        ->where('product_id', $product->id)
                        ->firstOrFail();

                    if ($variant->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'message' => "Insufficient stock for {$product->name} ({$variant->display_name})",
                        ], 422);
                    }

                    $unitPrice += (float) $variant->price_adjustment;
                }

                $totalPrice = $unitPrice * $item['quantity'];
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            // Create order
            $shippingCost = 0; // Free shipping for now
            $total = $subtotal + $shippingCost;
            $paymentMethod = $validated['payment_method'];

            $order = Order::create([
                'user_id' => $request->user()?->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_wilaya' => $validated['shipping_wilaya'],
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items and update stock
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_variant_id' => $item['variant']?->id,
                    'product_name' => $item['product']->name,
                    'variant_info' => $item['variant']?->display_name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Decrease stock
                if ($item['variant']) {
                    $item['variant']->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Handle Chargily payment
            if ($paymentMethod === 'chargily') {
                try {
                    $checkout = $this->chargilyService->createCheckout($order);
                    $order->update(['chargily_checkout_id' => $checkout['checkout_id']]);

                    return response()->json([
                        'message' => 'Order created. Redirecting to payment...',
                        'data' => $order->load('items'),
                        'checkout_url' => $checkout['checkout_url'],
                    ], 201);
                } catch (\Exception $e) {
                    // Rollback will happen automatically
                    throw $e;
                }
            }

            return response()->json([
                'message' => 'Order placed successfully',
                'data' => $order->load('items'),
            ], 201);
        });
    }

    /**
     * Create order for guest users.
     */
    public function guestCheckout(Request $request): JsonResponse
    {
        // Same validation but without user
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_wilaya' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'sometimes|in:cod,chargily',
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $variant = null;
                $unitPrice = (float) $product->price;

                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::where('id', $item['variant_id'])
                        ->where('product_id', $product->id)
                        ->firstOrFail();

                    if ($variant->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'message' => "Insufficient stock for {$product->name} ({$variant->display_name})",
                        ], 422);
                    }

                    $unitPrice += (float) $variant->price_adjustment;
                }

                $totalPrice = $unitPrice * $item['quantity'];
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            $shippingCost = 0;
            $total = $subtotal + $shippingCost;
            $paymentMethod = $validated['payment_method'] ?? 'cod';

            $order = Order::create([
                'user_id' => null,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_wilaya' => $validated['shipping_wilaya'],
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_variant_id' => $item['variant']?->id,
                    'product_name' => $item['product']->name,
                    'variant_info' => $item['variant']?->display_name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                if ($item['variant']) {
                    $item['variant']->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Handle Chargily payment
            if ($paymentMethod === 'chargily') {
                try {
                    $checkout = $this->chargilyService->createCheckout($order);
                    $order->update(['chargily_checkout_id' => $checkout['checkout_id']]);

                    return response()->json([
                        'message' => 'Order created. Redirecting to payment...',
                        'data' => $order->load('items'),
                        'checkout_url' => $checkout['checkout_url'],
                    ], 201);
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            return response()->json([
                'message' => 'Order placed successfully',
                'data' => $order->load('items'),
            ], 201);
        });
    }
}
