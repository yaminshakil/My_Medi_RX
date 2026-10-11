<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use Illuminate\Support\Collection;

interface PhysicianRepositoryInterface
{
    public function active(): Collection;
}
