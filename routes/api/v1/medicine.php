<?php

use App\Http\Controllers\Api\Medicines\ManufacturerController;
use App\Http\Controllers\Api\Medicines\MedicineController;
use App\Http\Controllers\Api\Medicines\MedicineDoseController;
use App\Http\Controllers\Api\Medicines\MedicineDurationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/manufacturers', ManufacturerController::class);
    Route::apiResource('/medicines', MedicineController::class);
    Route::apiResource('/medicines-doses', MedicineDoseController::class);
    Route::apiResource('/medicine-durations', MedicineDurationController::class);
});
