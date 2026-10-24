<?php

use App\Http\Controllers\Api\Hospitals\HospitalController;
use App\Http\Controllers\Api\V1\Medical\NearestMedicalFacilityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/hospitals', HospitalController::class);
    Route::get('/hospitals-types', [HospitalController::class, 'getAllHospitalType']);
});
Route::prefix('nearest-medical')->group(function () {
    Route::get(
        '/facilities',
        [NearestMedicalFacilityController::class, 'index']
    )->name('api.v1.nearest-medical.facilities');
});
