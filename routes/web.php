<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

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

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('user/profile', [ProfileController::class, 'index'])->name('user.profile');
});

Route::get('/divisions', [LocationController::class, 'getDivisions']);
Route::get('/districts/{division}', [LocationController::class, 'getDistricts']);
Route::get('/thanas/{district}', [LocationController::class, 'getThanas']);

Route::get('/language/{locale}', function ($locale) {
    if (!array_key_exists($locale, config('languages.supported'))) {
        abort(404);
    }

    session()->put('locale', $locale);

    app()->setLocale($locale);

    logger()->info('Language switched', [
        'selected' => $locale,
        'session' => session('locale'),
    ]);


    return back();
})->name('language.switch');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/doctor.php';
require __DIR__.'/patient.php';
require __DIR__.'/prescription.php';
require __DIR__.'/hospital.php';
require __DIR__.'/admin.php';
