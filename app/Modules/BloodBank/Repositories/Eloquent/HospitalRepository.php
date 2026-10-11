<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\Hospital;
use App\Modules\BloodBank\Repositories\Contracts\HospitalRepositoryInterface;

class HospitalRepository implements HospitalRepositoryInterface
{
    public function find(int $id): ?Hospital
    {
        return Hospital::find($id);
    }
}
