<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Medicines\ManufacturerController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/manufacturers', ManufacturerController::class);
});
