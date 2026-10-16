<?php

use App\Http\Controllers\Admin\Hospitals\DepartmentController;
use App\Http\Controllers\Admin\Hospitals\DoctorRosterController;
use App\Http\Controllers\Admin\Hospitals\DoctorRosterLookupController;
use App\Http\Controllers\Admin\Hospitals\HospitalController;
use App\Http\Controllers\Admin\Hospitals\HospitalVerificationController;
use App\Http\Controllers\Frontend\Diagnostic\DiagnosticController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/diagnostics', [DiagnosticController::class, 'index'])->name('diagnostics.index');
    Route::get('/diagnostics/{hospital}', [DiagnosticController::class, 'show'])->name('diagnostics.show');
});
Route::prefix('admin')->middleware(['auth', 'verified', 'role.redirect:Admin'])->group(function () {
    /* Hospital CRUD */
    Route::get('/hospitals', [HospitalController::class, 'index'])->name('hospital.index');
    Route::get('/hospitals/create', [HospitalController::class, 'create'])->name('hospital.create');
    Route::post('/hospitals/create', [HospitalController::class, 'store']);
    Route::get('/hospitals/{id}/edit', [HospitalController::class, 'edit'])->name('hospital.edit');
    Route::post('/hospitals/{id}', [HospitalController::class, 'update'])->name('hospital.update');
    Route::delete('/hospitals/{id}/delete', [HospitalController::class, 'destroy'])->name('hospital.delete');

    Route::get('chamberschedule/{hospital_uuid}/doctor/{doctor_uuid}', [DiagnosticController::class, 'getChamberSchedule'])->name('updatechamber.schedule');
    Route::post('chamberschedule/{id}', [DiagnosticController::class, 'updateChamberSchedule'])->name('chamberschedule.update');
    Route::post('addservices/', [DiagnosticController::class, 'addDiagnosticServices'])->name('diagnostic.addservies');
    Route::post('services/update/{id}', [DiagnosticController::class, 'updateDiagnosticServices'])->name('diagnostic.servies.update');
    Route::delete('services/delete/{id}', [DiagnosticController::class, 'deleteDiagnosticServices'])->name('diagnostic.servies.destroy');
    Route::post('/diagnostics/{id}/rate', [DiagnosticController::class, 'storeRating'])->name('diagnostics.rate');
});

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get(
            'hospitals/{uuid}/verification',
            [HospitalVerificationController::class, 'show']
        )->name('hospital.verification');

        Route::post(
            'hospitals/{uuid}/verification/documents',
            [HospitalVerificationController::class, 'uploadDocument']
        )->name('hospital.verification.documents.store');

        Route::delete(
            'hospital-verification-documents/{id}',
            [HospitalVerificationController::class, 'destroyDocument']
        )->name('hospital.verification.documents.destroy');

        Route::post(
            'hospital-verification-documents/{id}/approve',
            [HospitalVerificationController::class, 'approveDocument']
        )->name('hospital.verification.documents.approve');

        Route::post(
            'hospital-verification-documents/{id}/reject',
            [HospitalVerificationController::class, 'rejectDocument']
        )->name('hospital.verification.documents.reject');

        Route::post(
            'hospitals/{uuid}/verification/approve',
            [HospitalVerificationController::class, 'approveHospital']
        )->name('hospital.verification.approve');

        Route::post(
            'hospitals/{uuid}/verification/reject',
            [HospitalVerificationController::class, 'rejectHospital']
        )->name('hospital.verification.reject');

        Route::post(
            'hospitals/{uuid}/verification/suspend',
            [HospitalVerificationController::class, 'suspendHospital']
        )->name('hospital.verification.suspend');
    });

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::resource('departments', DepartmentController::class)
        ->parameters(['departments' => 'department'])
        ->names('department');

    Route::post('departments/{department}/doctors', [DepartmentController::class, 'assignDoctor'])
        ->name('department.doctors.store');
    Route::put('departments/{department}/doctors/{user}', [DepartmentController::class, 'updateDoctor'])
        ->name('department.doctors.update');
    Route::delete('departments/{department}/doctors/{user}', [DepartmentController::class, 'removeDoctor'])
        ->name('department.doctors.destroy');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/doctor-roster', [DoctorRosterController::class, 'index'])
        ->name('doctor-roster.index');

    Route::post('/doctor-shifts', [DoctorRosterController::class, 'storeShift'])->name('doctor-shifts.store');
    Route::put('/doctor-shifts/{shift}', [DoctorRosterController::class, 'updateShift'])->name('doctor-shifts.update');
    Route::delete('/doctor-shifts/{shift}', [DoctorRosterController::class, 'deleteShift'])->name('doctor-shifts.destroy');

    Route::post('/doctor-availabilities', [DoctorRosterController::class, 'storeAvailability'])->name('doctor-availabilities.store');
    Route::put('/doctor-availabilities/{availability}', [DoctorRosterController::class, 'updateAvailability'])->name('doctor-availabilities.update');
    Route::delete('/doctor-availabilities/{availability}', [DoctorRosterController::class, 'deleteAvailability'])->name('doctor-availabilities.destroy');

    Route::post('/doctor-rosters', [DoctorRosterController::class, 'storeRoster'])->name('doctor-rosters.store');
    Route::put('/doctor-rosters/{roster}', [DoctorRosterController::class, 'updateRoster'])->name('doctor-rosters.update');
    Route::delete('/doctor-rosters/{roster}', [DoctorRosterController::class, 'deleteRoster'])->name('doctor-rosters.destroy');

    Route::post('/doctor-leaves', [DoctorRosterController::class, 'storeLeave'])->name('doctor-leaves.store');
    Route::patch('/doctor-leaves/{leave}/approve', [DoctorRosterController::class, 'approveLeave'])->name('doctor-leaves.approve');
    Route::patch('/doctor-leaves/{leave}/reject', [DoctorRosterController::class, 'rejectLeave'])->name('doctor-leaves.reject');
});

Route::prefix('admin/api')->middleware('auth')->group(function () {
    Route::get(
        '/hospitals/{hospital:id}/departments',
        [DoctorRosterLookupController::class, 'departments']
    );

    Route::get(
        '/departments/{department}/doctors',
        [DoctorRosterLookupController::class, 'doctors']
    );
});
