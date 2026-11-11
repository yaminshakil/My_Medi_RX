<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\Hospital as BloodBankHospital;

interface HospitalRepositoryInterface
{
    public function find(int $id): ?BloodBankHospital;
}
