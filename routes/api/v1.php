<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/v1/auth.php';

Route::middleware('auth:api')
    ->group(function () {
        require base_path('routes/api/v1/doctor.php');
        require base_path('routes/api/v1/patient.php');
        require base_path('routes/api/v1/medicine.php');
        require base_path('routes/api/v1/hospital.php');
        require base_path('routes/api/v1/appointment.php');
        require base_path('routes/api/v1/prescription.php');
    });
