<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\Hospital;

interface HospitalRepositoryInterface
{
    public function find(int $id): ?Hospital;
}
