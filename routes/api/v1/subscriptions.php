<?php

use App\Http\Controllers\Api\Admin\SubscriptionManagementController as AdminSubscriptionController;
use App\Http\Controllers\Api\Admin\SubscriptionPlanController as AdminPlanController;
use App\Http\Controllers\Api\SslCommerzCallbackController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

// Public gateway callbacks: API middleware is stateless (no web CSRF token).
Route::prefix('/payments/sslcommerz')->name('api.sslcommerz.')->group(function () {
    Route::post('/success', [SslCommerzCallbackController::class, 'success'])->name('success');
    Route::post('/fail', [SslCommerzCallbackController::class, 'fail'])->name('fail');
    Route::post('/cancel', [SslCommerzCallbackController::class, 'cancel'])->name('cancel');
    Route::post('/ipn', [SslCommerzCallbackController::class, 'ipn'])->name('ipn');
});

Route::middleware('auth:api')->group(function () {
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);
    Route::get('/subscription-plans/{plan}', [SubscriptionPlanController::class, 'show']);

    Route::prefix('subscriptions')->group(function () {
        Route::get('/current', [SubscriptionController::class, 'current']);
        Route::get('/history', [SubscriptionController::class, 'history']);
        Route::get('/features', [SubscriptionController::class, 'features']);
        Route::post('/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('/free-activate', [SubscriptionController::class, 'activateFree']);
        Route::post('/{subscription}/renew', [SubscriptionController::class, 'renew']);
        Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    });

    Route::get('/subscription-payments', [SubscriptionController::class, 'payments']);
    Route::get('/subscription-payments/{payment}', [SubscriptionController::class, 'payment']);

    // Admin API. The controller enforces the Admin role explicitly.
    Route::middleware('admin.api')->prefix('admin/subscriptions')->group(function () {
        Route::get('/', [AdminSubscriptionController::class, 'subscriptions']);
        Route::get('/payments', [AdminSubscriptionController::class, 'payments']);
        Route::get('/payments/{payment}/review', [AdminSubscriptionController::class, 'reviewPayment']);
        Route::post('/payments/{payment}/approve', [AdminSubscriptionController::class, 'approvePayment']);
        Route::apiResource('plans', AdminPlanController::class);
    });
});
