<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\Patient;
use App\Modules\BloodBank\Repositories\Contracts\PatientRepositoryInterface;
use Illuminate\Support\Collection;

class PatientRepository implements PatientRepositoryInterface
{
    public function search(string $term, int $limit = 8): Collection
    {
        return Patient::query()
            ->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('mrn', 'like', "%{$term}%")
                ->orWhere('clinical_file_no', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function find(int $id): ?Patient
    {
        return Patient::find($id);
    }

    public function findOrFail(int $id): Patient
    {
        return Patient::findOrFail($id);
    }
}
