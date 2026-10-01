<?php

use App\Http\Controllers\Api\Patients\PatientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/patients', PatientController::class);
});
