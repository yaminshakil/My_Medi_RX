<?php

use App\Http\Controllers\Api\Doctors\DoctorController;
use App\Http\Controllers\Api\Doctors\DoctorEducationController;
use App\Http\Controllers\Api\Doctors\DoctorExperienceController;
use App\Http\Controllers\Api\Doctors\MedicalSpecialtyController;
use App\Http\Controllers\Api\V1\Review\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'profile.exists', 'role.redirect:Doctor'])->group(function () {
    Route::get('/medical-specialties', [MedicalSpecialtyController::class, 'index']);
    Route::apiResource('/doctors', DoctorController::class)->except('show');
    Route::apiResource('/doctors/experience', DoctorExperienceController::class);
    Route::apiResource('/doctors/education', DoctorEducationController::class);
});

Route::get('/doctors/{id}', [DoctorController::class, 'show'])->name('doctors.show');
Route::post('/doctor-ratings', [ReviewController::class, 'store'])->name('doctor.ratings.store');
