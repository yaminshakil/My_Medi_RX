<?php

use App\Http\Controllers\Admin\MenstrualCycle\MenstrualCycleLogController;
use App\Http\Controllers\Admin\MenstrualCycle\MenstrualCycleProfileController;
use App\Http\Controllers\Admin\Patients\PatientController;
use App\Http\Controllers\Admin\Patients\PatientProfileController;
use App\Http\Controllers\Admin\Vaccination\VaccinationController;
use App\Http\Controllers\Admin\Vaccination\VaccinationScheduleController;
use App\Http\Controllers\Profile\ProfileController;
use App\Modules\BloodBank\Http\Controllers\PatientBloodBankController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::prefix('admin/menstrual-cycle')
    ->name('admin.menstrual-cycle.')
    ->middleware(['auth', 'role.redirect:Admin,Doctor'])
    ->group(function () {
        Route::get('/profiles', [
            MenstrualCycleProfileController::class,
            'index',
        ])->name('profiles.index');

        Route::get('/profiles/{profile}/edit', [
            MenstrualCycleProfileController::class,
            'edit',
        ])->name('profiles.edit');

        Route::put('/profiles/{profile}', [
            MenstrualCycleProfileController::class,
            'update',
        ])->name('profiles.update');

        Route::delete('/profiles/{profile}', [
            MenstrualCycleProfileController::class,
            'destroy',
        ])->name('profiles.destroy');

        Route::get('/logs', [
            MenstrualCycleLogController::class,
            'index',
        ])->name('logs.index');
    });

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/pediatric-dose', function () {
        return Inertia::render('Admin/PediatricDose/Index');
    })->name('pediatric-dose.index');
});

Route::middleware(['auth'])->prefix('admin/vaccinations')->name('admin.vaccinations.')->group(function () {
    // Patient vaccination records
    Route::get('/', [VaccinationController::class, 'index'])
        ->name('index');

    Route::get('/create', [VaccinationController::class, 'create'])
        ->name('create');

    Route::post('/', [VaccinationController::class, 'store'])
        ->name('store');

    Route::get('/{record}/edit', [VaccinationController::class, 'edit'])
        ->name('edit');

    Route::put('/{record}', [VaccinationController::class, 'update'])
        ->name('update');

    Route::delete('/{record}', [VaccinationController::class, 'destroy'])
        ->name('destroy');
});

Route::middleware(['auth', 'role.redirect:Admin,Patient'])
    ->prefix('vaccination-schedules')
    ->name('vaccination-schedules.')
    ->group(function () {
        Route::get('/', [VaccinationScheduleController::class, 'index'])
            ->name('index');

        Route::get('/create', [VaccinationScheduleController::class, 'create'])
            ->name('create');

        Route::post('/', [VaccinationScheduleController::class, 'store'])
            ->name('store');

        Route::get('/{schedule}/edit', [VaccinationScheduleController::class, 'edit'])
            ->name('edit');

        Route::put('/{schedule}', [VaccinationScheduleController::class, 'update'])
            ->name('update');

        Route::delete('/{schedule}', [VaccinationScheduleController::class, 'destroy'])
            ->name('destroy');
    });

Route::middleware(['auth', 'role.redirect:Patient'])
    ->prefix('patient/blood-bank')
    ->name('patient.blood-bank.')
    ->group(function () {
        Route::get('/', [PatientBloodBankController::class, 'index'])->name('index');
        Route::post('/emergency', [PatientBloodBankController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('emergency.store');
        Route::post('/donor-profile', [PatientBloodBankController::class, 'registerDonor'])->name('donor-profile.store');
        Route::patch('/donor-profile', [PatientBloodBankController::class, 'updateDonorProfile'])->name('donor-profile.update');
        Route::patch('/donor-profile/availability', [PatientBloodBankController::class, 'updateDonorAvailability'])
            ->name('donor-profile.availability');
        Route::patch('/emergency/{emergencyRequest}/cancel', [PatientBloodBankController::class, 'cancel'])
            ->name('emergency.cancel');
    });
