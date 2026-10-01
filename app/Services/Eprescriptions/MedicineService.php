<?php

namespace App\Services\Eprescriptions;

use App\Interfaces\Eprescriptions\MedicineRepositoryInterface;
use App\Models\Medicine;

class MedicineService
{
    protected MedicineRepositoryInterface $repository;

    public function __construct(MedicineRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list($perPage, $search)
    {
        return $this->repository->all($perPage, $search);
    }

    public function get(int $id): ?Medicine
    {
        return $this->repository->find($id);
    }

    public function store(array $data): Medicine
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function destroy(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getManufacturer()
    {
        return $this->repository->getManufacturer();
    }
}
