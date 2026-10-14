<?php

use App\Http\Controllers\Api\V1\Appointment\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('/appointments', AppointmentController::class);
});
