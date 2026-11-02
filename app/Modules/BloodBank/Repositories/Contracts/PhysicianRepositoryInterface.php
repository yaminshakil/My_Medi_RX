<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\Physician;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PhysicianRepositoryInterface
{
    /** Active physicians for the issue form dropdown. */
    public function active(): Collection;

    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): Physician;

    public function setActive(Physician $physician, bool $active): Physician;
}
