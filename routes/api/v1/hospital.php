<?php

use App\Http\Controllers\Api\Hospitals\HospitalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/hospitals', HospitalController::class);
    Route::get('/hospitals/types', [HospitalController::class, 'getHospitalType']);
});
