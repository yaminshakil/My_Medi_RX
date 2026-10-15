<?php

use App\Http\Controllers\Api\Patients\PatientController;
use App\Http\Controllers\Api\V1\Patient\PatientDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Patient\MedicationController;
use App\Http\Controllers\Api\V1\Patient\MedicationScheduleController;
use App\Http\Controllers\Api\V1\Patient\MedicationLogController;
use App\Http\Controllers\Api\V1\Patient\MedicationAdherenceController;
use App\Http\Controllers\Api\V1\Patient\MedicationScanController;
use App\Http\Controllers\Api\V1\Patient\MedicationRefillController;
use App\Http\Controllers\Api\V1\Patient\VaccinationController;
use App\Http\Controllers\Api\V1\Patient\MedicationReportController;

Route::middleware(['auth:api'])->group(function () {
});

Route::prefix('v1/patient')
    ->middleware('auth:api')
    ->group(function () {
        // Dashboard and medication CRUD
        Route::apiResource('/patients', PatientController::class);
        Route::get('/patient/dashboard/overview', [PatientDashboardController::class, 'index']);

        Route::get('/medications/dashboard', [
            MedicationController::class, 'dashboard'
        ]);
        Route::apiResource('medications', MedicationController::class);

        // Medication schedules
        Route::get('/medications/{medication}/schedules', [
            MedicationScheduleController::class, 'index'
        ]);
        Route::post('/medications/{medication}/schedules', [
            MedicationScheduleController::class, 'store'
        ]);
        Route::put('/medication-schedules/{schedule}', [
            MedicationScheduleController::class, 'update'
        ]);
        Route::delete('/medication-schedules/{schedule}', [
            MedicationScheduleController::class, 'destroy'
        ]);

        // Dose tracking
        Route::get('/medication-logs', [
            MedicationLogController::class, 'index'
        ]);
        Route::post('/medication-logs/{log}/taken', [
            MedicationLogController::class, 'taken'
        ]);
        Route::post('/medication-logs/{log}/skip', [
            MedicationLogController::class, 'skip'
        ]);
        Route::post('/medication-logs/{log}/snooze', [
            MedicationLogController::class, 'snooze'
        ]);

        // Adherence analytics
        Route::get('/medications/adherence', [
            MedicationAdherenceController::class, 'summary'
        ]);
        Route::get('/medications/adherence/calendar', [
            MedicationAdherenceController::class, 'calendar'
        ]);
        Route::get('/medications/adherence/streaks', [
            MedicationAdherenceController::class, 'streaks'
        ]);

        // Prescription scanning
        Route::post('/medications/scan/qr', [
            MedicationScanController::class, 'qr'
        ]);
        Route::post('/medications/scan/ocr', [
            MedicationScanController::class, 'ocr'
        ]);

        // Inventory
        Route::get('/medications/refills', [
            MedicationRefillController::class, 'index'
        ]);
        Route::post('/medications/{medication}/refill', [
            MedicationRefillController::class, 'store'
        ]);

        // Vaccinations
        Route::get('/vaccinations', [
            VaccinationController::class, 'index'
        ]);
        Route::post('/vaccinations', [
            VaccinationController::class, 'store'
        ]);
        Route::get('/vaccinations/{record}', [
            VaccinationController::class, 'show'
        ]);
        Route::put('/vaccinations/{record}', [
            VaccinationController::class, 'update'
        ]);
        Route::delete('/vaccinations/{record}', [
            VaccinationController::class, 'destroy'
        ]);

        // Reports
        Route::get('/medications/reports', [
            MedicationReportController::class, 'index'
        ]);
        Route::get('/medications/reports/pdf', [
            MedicationReportController::class, 'pdf'
        ]);
    });
