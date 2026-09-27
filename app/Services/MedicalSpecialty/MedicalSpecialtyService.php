<?php

namespace App\Services\MedicalSpecialty;

use App\Interfaces\MedicalSpecialty\MedicalSpecialtyRepositoryInterface;
use App\Models\MedicalSpecialty;

class MedicalSpecialtyService
{
    protected $repo;

    public function __construct(MedicalSpecialtyRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getAll($search, $perPage)
    {
        return $this->repo->all($search, $perPage);
    }

    public function getById($id): ?MedicalSpecialty
    {
        return $this->repo->find($id);
    }

    public function store(array $data): MedicalSpecialty
    {
        return $this->repo->create($data);
    }

    public function update(MedicalSpecialty $specialty, array $data): MedicalSpecialty
    {
        return $this->repo->update($specialty, $data);
    }

    public function destroy(MedicalSpecialty $specialty): bool
    {
        return $this->repo->delete($specialty);
    }
}
