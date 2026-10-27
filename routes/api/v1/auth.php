<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\UserPasswordResetController;
use App\Http\Controllers\Api\FirebaseDeviceTokenController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// Password Reset Endpoints (no authentication required)
Route::post('/auth/password/email', [UserPasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/auth/password/reset', [UserPasswordResetController::class, 'reset']);
Route::post('/auth/password/send-otp', [UserPasswordResetController::class, 'sendOtp']);
Route::post('/auth/password/verify-otp', [UserPasswordResetController::class, 'verifyOtp']);
Route::post('/auth/password/change', [UserPasswordResetController::class, 'changePassword']);

// Protected API routes using Access Token

Route::middleware(['auth:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/device-token', [FirebaseDeviceTokenController::class, 'store'])
        ->name('device-token.store');
    Route::get('/user', [AuthController::class, 'user']);
});

// Refresh Token route
Route::middleware(['auth:api'])->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
});
