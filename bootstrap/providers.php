<?php

use App\Modules\BloodBank\Providers\BloodBankServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\DoctorServiceProvider;
use App\Providers\PrescriptionServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    DoctorServiceProvider::class,
    RepositoryServiceProvider::class,
    PrescriptionServiceProvider::class,
    BloodBankServiceProvider::class,
];
