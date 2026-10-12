<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Medicines\ManufacturerController;
use App\Http\Controllers\Api\Medicines\MedicineController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/manufacturers', ManufacturerController::class);
    Route::apiResource('/medicines', MedicineController::class);
});
