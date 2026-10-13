<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Medicines\ManufacturerController;
use App\Http\Controllers\Api\Medicines\MedicineController;
use App\Http\Controllers\Api\Medicines\MedicineDoseController;
use App\Http\Controllers\Api\Medicines\MedicineDurationController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/manufacturers', ManufacturerController::class);
    Route::apiResource('/medicines', MedicineController::class);
    Route::apiResource('/medicines-doses', MedicineDoseController::class);
    Route::apiResource('/medicine-durations', MedicineDurationController::class);
});
