<?php

use App\Http\Controllers\Api\V1\Prescriptions\PrescriptionController;
use App\Http\Controllers\Api\V1\Prescriptions\PrescriptionVerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('patient')->middleware(['auth:api', 'profile.exists'])->group(function () {
    Route::apiResource('prescriptions', PrescriptionController::class);
    Route::get('prescriptions/medications/{id}', [PrescriptionController::class, 'getPrescriMedication']);

    Route::get('/prescriptions/verify/{token}', [PrescriptionVerificationController::class, 'verify'])->name('prescriptions.verify');

    Route::post('/prescriptions/{prescription}/generate-qr', [PrescriptionVerificationController::class, 'generateQr']);
});
