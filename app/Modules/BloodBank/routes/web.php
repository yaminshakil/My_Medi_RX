<?php

use App\Modules\BloodBank\Http\Controllers\BloodBagController;
use App\Modules\BloodBank\Http\Controllers\BloodIssueController;
use App\Modules\BloodBank\Http\Controllers\DashboardController;
use App\Modules\BloodBank\Http\Controllers\DonorController;
use App\Modules\BloodBank\Http\Controllers\DonorResponseController;
use App\Modules\BloodBank\Http\Controllers\EmergencyRequestController;
use Illuminate\Support\Facades\Route;

$prefix = config('blood-bank.route_prefix', 'blood-bank');

// Staff area: hospital admins / blood bank staff only (see the Gate in BloodBankServiceProvider).
Route::middleware(['web', 'auth', 'can:manage-blood-bank'])
    ->prefix($prefix)
    ->name('blood-bank.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        // Blood units (batch + expiry tracking)
        Route::get('bags', [BloodBagController::class, 'index'])->name('bags.index');
        Route::get('bags/create', [BloodBagController::class, 'create'])->name('bags.create');
        Route::post('bags', [BloodBagController::class, 'store'])->name('bags.store');
        Route::patch('bags/{bag}/discard', [BloodBagController::class, 'discard'])->name('bags.discard');
        Route::patch('bags/{bag}/release', [BloodBagController::class, 'release'])->name('bags.release');

        // Transfusion & issue log. No delete route on purpose: the log is an audit trail.
        Route::get('issues', [BloodIssueController::class, 'index'])->name('issues.index');
        Route::get('issues/create', [BloodIssueController::class, 'create'])->name('issues.create');
        Route::post('issues', [BloodIssueController::class, 'store'])->name('issues.store');
        Route::get('issues/{issue}', [BloodIssueController::class, 'show'])->name('issues.show');
        Route::patch('issues/{issue}/outcome', [BloodIssueController::class, 'outcome'])->name('issues.outcome');

        // Emergency broadcast
        Route::get('emergency', [EmergencyRequestController::class, 'index'])->name('emergency.index');
        Route::post('emergency', [EmergencyRequestController::class, 'store'])->name('emergency.store');
        Route::get('emergency/{emergencyRequest}', [EmergencyRequestController::class, 'show'])->name('emergency.show');
        Route::patch('emergency/{emergencyRequest}/fulfill', [EmergencyRequestController::class, 'fulfill'])->name('emergency.fulfill');
        Route::patch('emergency/{emergencyRequest}/cancel', [EmergencyRequestController::class, 'cancel'])->name('emergency.cancel');

        // Donors
        Route::get('donors', [DonorController::class, 'index'])->name('donors.index');
        Route::post('donors', [DonorController::class, 'store'])->name('donors.store');
        Route::patch('donors/{donor}/verify', [DonorController::class, 'verify'])->name('donors.verify');
    });

// Donor replies from the email link: public, but the URL must carry a valid signature.
Route::middleware(['web', 'signed'])
    ->get($prefix.'/emergency/{emergencyRequest}/respond/{donor}/{response}', DonorResponseController::class)
    ->whereIn('response', ['accepted', 'declined'])
    ->name('blood-bank.donor-response');
