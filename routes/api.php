<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChargilyWebhookController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Categories
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::get('/categories/{slug}', [ProductController::class, 'byCategory']);

    // Guest checkout
    Route::post('/checkout/guest', [OrderController::class, 'guestCheckout']);

    // Chargily webhook (no auth needed)
    Route::post('/webhooks/chargily', [ChargilyWebhookController::class, 'handle']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user/profile', [AuthController::class, 'updateProfile']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
        Route::post('/checkout', [OrderController::class, 'store']);
    });
});
