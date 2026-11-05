<?php

use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeederController;
use App\Http\Controllers\SslCommerzController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('install', function () {
    try {
        // Run migrations
        Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('storage:link');
        // Define the source and destination paths
        Artisan::call('optimize:clear');

        return [
            'success' => true,
            'message' => 'Installation record created successfully.',
        ];
    } catch (Exception $e) {
        Log::error('InstallRepository error: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'data'    => null,
            'message' => 'Failed to create installation record.',
        ];
    }
});

Route::get('migrate', function () {
    try {
        // Run migrations
        Artisan::call('migrate');
        // Define the source and destination paths
        Artisan::call('optimize:clear');

        return [
            'success' => true,
            'message' => 'Migration created successfully.',
        ];
    } catch (Exception $e) {
        Log::error('InstallRepository error: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'data'    => null,
            'message' => 'Failed to create Migration record.',
        ];
    }
});

Route::get('/clear', function () {
    try {
        // Run migrations
        Artisan::call('storage:link');
        Artisan::call('optimize:clear');

        return [
            'success' => true,
            'message' => 'Cache clear successfully.',
        ];
    } catch (Exception $e) {
        Log::error('Cache clear error: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'data'    => null,
            'message' => 'Failed to Cache clear.',
        ];
    }
});

Route::get('/run-seeder', [SeederController::class, 'runSeeder'])
    ->middleware(['auth', 'verified']);

Route::prefix('admin')->middleware(['auth', 'verified', 'profile.exists'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('user/profile', [ProfileController::class, 'index'])->name('user.profile');
});

Route::get('/divisions', [LocationController::class, 'getDivisions']);
Route::get('/districts/{division}', [LocationController::class, 'getDistricts']);
Route::get('/thanas/{district}', [LocationController::class, 'getThanas']);

Route::get('/language/{locale}', function ($locale) {
    if (! array_key_exists($locale, config('languages.supported'))) {
        abort(404);
    }

    session()->put('locale', $locale);

    app()->setLocale($locale);

    logger()->info('Language switched', [
        'selected' => $locale,
        'session'  => session('locale'),
    ]);

    return back();
})->name('language.switch');

Route::middleware('auth')->group(function () {
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/{plan}/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::get('/subscriptions/history', [SubscriptionController::class, 'history'])->name('subscriptions.history');
    Route::get('/subscriptions/result', [SubscriptionController::class, 'result'])->name('subscriptions.result');
});
Route::post('/payments/sslcommerz/success', [SslCommerzController::class, 'success'])->name('sslcommerz.success');
Route::post('/payments/sslcommerz/fail', [SslCommerzController::class, 'fail'])->name('sslcommerz.fail');
Route::post('/payments/sslcommerz/cancel', [SslCommerzController::class, 'cancel'])->name('sslcommerz.cancel');
Route::post('/payments/sslcommerz/ipn', [SslCommerzController::class, 'ipn'])->name('sslcommerz.ipn');

Route::middleware(['auth', 'role:Admin,web'])
    ->prefix('admin/subscription-plans')
    ->name('admin.subscription-plans.')
    ->group(function () {
        Route::get('/', [SubscriptionPlanController::class, 'index'])
            ->name('index');

        Route::post('/', [SubscriptionPlanController::class, 'store'])
            ->name('store');

        Route::match(['put', 'patch'], '/{plan}', [SubscriptionPlanController::class, 'update'])
            ->name('update');

        Route::delete('/{plan}', [SubscriptionPlanController::class, 'destroy'])
            ->name('destroy');
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/doctor.php';
require __DIR__.'/patient.php';
require __DIR__.'/prescription.php';
require __DIR__.'/hospital.php';
require __DIR__.'/admin.php';
