<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\UserPasswordResetController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// Password Reset Endpoints (no authentication required)
Route::post('password/email', [UserPasswordResetController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [UserPasswordResetController::class, 'reset']);
Route::post('password/send-otp', [UserPasswordResetController::class, 'sendOtp']);
Route::post('password/verify-otp', [UserPasswordResetController::class, 'verifyOtp']);
Route::post('password/change', [UserPasswordResetController::class, 'changePassword']);

// Protected API routes using Access Token

Route::middleware(['auth:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Refresh Token route
Route::middleware(['auth:api'])->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
});
