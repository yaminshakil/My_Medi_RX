<?php

use App\Providers\AppServiceProvider;
use App\Providers\DoctorServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\PrescriptionServiceProvider;

return [
    AppServiceProvider::class,
    DoctorServiceProvider::class,
    RepositoryServiceProvider::class,
    PrescriptionServiceProvider::class,
];
