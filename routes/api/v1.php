<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/v1/auth.php';

Route::middleware('auth:sanctum')
    ->group(function () {
        require base_path('routes/api/v1/doctor.php');
    });
