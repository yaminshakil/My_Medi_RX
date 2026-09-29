<?php

use App\Http\Controllers\Api\Doctors\DoctorController;
use App\Http\Controllers\Api\Doctors\MedicalSpecialtyController;
use App\Http\Controllers\Api\Doctors\DoctorExperienceController;
use App\Http\Controllers\Api\Doctors\DoctorEducationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/medical-specialties', [MedicalSpecialtyController::class, 'index']);
    Route::apiResource('/doctors', DoctorController::class);
    Route::apiResource('/doctors/experience', DoctorExperienceController::class);
    Route::apiResource('/doctors/education', DoctorEducationController::class);
});
