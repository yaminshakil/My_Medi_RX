<?php

use App\Http\Controllers\Api\V1\Appointment\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'profile.exists'])->group(function () {
    Route::get('appointments/avalabletimeline/{doctor_id}/{chamber_id?}', [AppointmentController::class, 'avalableTimeline'])->name('appointments.avalabletimeline');
    Route::apiResource('/appointments', AppointmentController::class);
});
