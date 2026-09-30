<?php

use App\Http\Controllers\Admin\Patients\PatientController;
use App\Http\Controllers\Admin\Patients\PatientProfileController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/pateint', function () {
        return redirect()->intended('/dashboard');
    });
});
Route::prefix('admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/pateint/profile', [ProfileController::class, 'getPatientProfile'])->name('patients.profile.create');
    Route::post('/pateint/profile/create', [PatientProfileController::class, 'store'])->name('patients.profile.store');
    Route::get('/pateint/profile/{id}/edit', [PatientProfileController::class, 'edit'])->name('patients.profile.edit');
    Route::post('/pateint/profile/{id}/update', [PatientProfileController::class, 'update'])->name('patients.profile.update');
    Route::get('/pateint/profile/{id}', [PatientProfileController::class, 'show'])->name('patients.profile.show');
    Route::delete('/pateint/profile/{id}/delete', [PatientProfileController::class, 'destroy'])->name('patients.profile.destroy');
    Route::delete('/pateint/appointment/{id}/delete', [PatientProfileController::class, 'appointmentDestroy'])->name('patients.appointment.destroy');
});

Route::prefix('admin')->middleware(['web', 'auth', 'profile.exists', 'role.redirect:Admin,Doctor,Assistant'])->group(function () {
    Route::resource('patients', PatientController::class);
});
