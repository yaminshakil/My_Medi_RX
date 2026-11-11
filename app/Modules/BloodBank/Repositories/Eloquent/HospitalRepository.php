<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\Hospital as BloodBankHospital;
use App\Modules\BloodBank\Repositories\Contracts\HospitalRepositoryInterface;

class HospitalRepository implements HospitalRepositoryInterface
{
    public function find(int $id): ?BloodBankHospital
    {
        return BloodBankHospital::find($id);
    }
}
