<?php

use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SiteSettingsController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/v1/auth.php';

Route::get('/settings', SiteSettingsController::class)
    ->name('settings.index');

Route::middleware('auth:api')
    ->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        require base_path('routes/api/v1/doctor.php');
        require base_path('routes/api/v1/patient.php');
        require base_path('routes/api/v1/medicine.php');
        require base_path('routes/api/v1/hospital.php');
        require base_path('routes/api/v1/appointment.php');
        require base_path('routes/api/v1/prescription.php');
        require base_path('routes/api/v1/users.php');
        require base_path('routes/api/v1/pharmacies.php');
    });
