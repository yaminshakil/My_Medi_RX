<?php

use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyLicenseController;
use App\Http\Controllers\Api\V1\Pharmacy\PharmacyRegistrationController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/v1/auth.php';

Route::middleware('auth:api')
    ->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');
        Route::post('/pharmacies', [PharmacyRegistrationController::class, 'store'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.store');
        Route::post('/pharmacies/licenses', [PharmacyLicenseController::class, 'store'])
            ->middleware('role:Pharmacy,api')
            ->name('pharmacies.licenses.store');

        require base_path('routes/api/v1/doctor.php');
        require base_path('routes/api/v1/patient.php');
        require base_path('routes/api/v1/medicine.php');
        require base_path('routes/api/v1/hospital.php');
        require base_path('routes/api/v1/appointment.php');
        require base_path('routes/api/v1/prescription.php');
        require base_path('routes/api/v1/users.php');
    });
