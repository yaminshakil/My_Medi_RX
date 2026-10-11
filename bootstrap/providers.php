<?php

use App\Providers\AppServiceProvider;
use App\Providers\DoctorServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\PrescriptionServiceProvider;
use App\Modules\BloodBank\Providers\BloodBankServiceProvider;

return [
    AppServiceProvider::class,
    DoctorServiceProvider::class,
    RepositoryServiceProvider::class,
    PrescriptionServiceProvider::class,
    BloodBankServiceProvider::class,
];
