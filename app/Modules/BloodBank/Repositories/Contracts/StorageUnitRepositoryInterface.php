<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use Illuminate\Support\Collection;

interface StorageUnitRepositoryInterface
{
    public function active(): Collection;
}
