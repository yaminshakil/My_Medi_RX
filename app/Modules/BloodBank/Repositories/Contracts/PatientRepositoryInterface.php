<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\Patient;
use Illuminate\Support\Collection;

interface PatientRepositoryInterface
{
    /** Match on name, MRN or clinical file number. */
    public function search(string $term, int $limit = 8): Collection;

    public function find(int $id): ?Patient;

    public function findOrFail(int $id): Patient;
}
