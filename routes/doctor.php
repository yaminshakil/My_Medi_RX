<?php

use App\Http\Controllers\Admin\Doctors\DoctorEducationController;
use App\Http\Controllers\Admin\Doctors\DoctorExperienceController;
use App\Http\Controllers\Admin\Doctors\ManageDoctorController;
use App\Http\Controllers\Admin\MedicalSpecialty\MedicalSpecialtyController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/doctor', function () {
        return redirect()->intended('/dashboard');
    });
});

Route::prefix('admin/doctor')->middleware(['web', 'auth', 'role.redirect:Doctor'])->group(function () {
    Route::get('profile', [ProfileController::class, 'getDoctorProfile'])->name('doctor.profile');
    Route::post('profile/create', [ProfileController::class, 'createDoctorProfile'])->name('doctor.profile.create');
    Route::post('profile/update', [ProfileController::class, 'updateDoctorProfile'])->name('doctor.profile.update');
});

Route::prefix('admin/doctor')->middleware(['web', 'auth', 'profile.exists', 'role.redirect:Doctor'])->group(function () {
    Route::get('educations', [DoctorEducationController::class, 'index'])->name('doctor.educations.index');
    Route::post('educations', [DoctorEducationController::class, 'store'])->name('doctor.educations.store');
    Route::post('educations/{id}', [DoctorEducationController::class, 'update'])->name('doctor.educations.update');
    Route::delete('educations/{id}', [DoctorEducationController::class, 'destroy'])->name('doctor.educations.destroy');

    Route::get('experiences', [DoctorExperienceController::class, 'index'])->name('doctor.experiences.index');
    Route::post('experiences', [DoctorExperienceController::class, 'store'])->name('doctor.experiences.store');
    Route::post('experiences/{id}', [DoctorExperienceController::class, 'update'])->name('doctor.experiences.update');
    Route::delete('experiences/{id}', [DoctorExperienceController::class, 'destroy'])->name('doctor.experiences.destroy');
});

Route::prefix('admin')->middleware(['web', 'profile.exists', 'auth', 'role.redirect:Admin,Doctor'])->group(function () {
    Route::post('/doctors/{doctor}/toggle-featured', [ManageDoctorController::class, 'toggleFeatured'])->name('doctors.toggleFeatured');
    Route::post('/doctors/{doctor}/toggle-active', [ManageDoctorController::class, 'toggleActive'])->name('doctors.toggleActive');

    Route::resource('doctors', ManageDoctorController::class)->except(['update']);
    Route::post('doctors/update/{doctor}', [ManageDoctorController::class, 'update'])->name('doctors.update');

    Route::resource('medical-specialties', MedicalSpecialtyController::class);
});
