<?php

use App\Modules\BloodBank\Http\Controllers\Api\BloodBagApiController;
use App\Modules\BloodBank\Http\Controllers\Api\BloodIssueApiController;
use App\Modules\BloodBank\Http\Controllers\Api\DashboardApiController;
use App\Modules\BloodBank\Http\Controllers\Api\DonorApiController;
use App\Modules\BloodBank\Http\Controllers\Api\EmergencyRequestApiController;
use App\Modules\BloodBank\Http\Controllers\Api\LookupApiController;
use App\Modules\BloodBank\Http\Controllers\Api\StorageUnitApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('blood-bank')
    ->name('blood-bank.')
    ->group(function (): void {
        Route::get('dashboard', DashboardApiController::class)->name('dashboard');
        Route::get('lookups', [LookupApiController::class, 'index'])->name('lookups.index');
        Route::get('components', [LookupApiController::class, 'components'])->name('components.index');
        Route::get('patients', [LookupApiController::class, 'patients'])->name('patients.index');
        Route::get('storage-units', [StorageUnitApiController::class, 'index'])->name('storage-units.index');

        Route::get('bags', [BloodBagApiController::class, 'index'])->name('bags.index');
        Route::post('bags', [BloodBagApiController::class, 'store'])->name('bags.store');
        Route::patch('bags/{bag}/discard', [BloodBagApiController::class, 'discard'])->name('bags.discard');
        Route::patch('bags/{bag}/release', [BloodBagApiController::class, 'release'])->name('bags.release');

        Route::get('issues', [BloodIssueApiController::class, 'index'])->name('issues.index');
        Route::get('issues/available-bags', [BloodIssueApiController::class, 'availableBags'])->name('issues.available-bags');
        Route::post('issues', [BloodIssueApiController::class, 'store'])->name('issues.store');
        Route::get('issues/{issue}', [BloodIssueApiController::class, 'show'])->name('issues.show');
        Route::patch('issues/{issue}/outcome', [BloodIssueApiController::class, 'outcome'])->name('issues.outcome');

        Route::get('emergency', [EmergencyRequestApiController::class, 'index'])->name('emergency.index');
        Route::post('emergency', [EmergencyRequestApiController::class, 'store'])->name('emergency.store');
        Route::get('emergency/{emergencyRequest}', [EmergencyRequestApiController::class, 'show'])->name('emergency.show');
        Route::patch('emergency/{emergencyRequest}/fulfill', [EmergencyRequestApiController::class, 'fulfill'])->name('emergency.fulfill');
        Route::patch('emergency/{emergencyRequest}/cancel', [EmergencyRequestApiController::class, 'cancel'])->name('emergency.cancel');

        Route::get('donors', [DonorApiController::class, 'index'])->name('donors.index');
        Route::post('donors', [DonorApiController::class, 'store'])->name('donors.store');
        Route::patch('donors/{donor}/verify', [DonorApiController::class, 'verify'])->name('donors.verify');
    });
