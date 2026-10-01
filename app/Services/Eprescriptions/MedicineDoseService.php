<?php

namespace App\Services\Eprescriptions;

use App\Interfaces\Eprescriptions\MedicineDoseRepositoryInterface;
use App\Models\MedicineDose;

class MedicineDoseService
{
    protected MedicineDoseRepositoryInterface $repository;

    public function __construct(MedicineDoseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll($perPage, $search)
    {
        return $this->repository->all($perPage, $search);
    }

    public function getById(int $id): ?MedicineDose
    {
        return $this->repository->find($id);
    }

    public function create(array $data): MedicineDose
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
