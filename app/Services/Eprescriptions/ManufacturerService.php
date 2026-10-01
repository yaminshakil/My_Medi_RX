<?php

namespace App\Services\Eprescriptions;

use App\Interfaces\Eprescriptions\ManufacturerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ManufacturerService
{
    protected ManufacturerRepositoryInterface $repository;

    public function __construct(ManufacturerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll($perPage, $search): LengthAwarePaginator
    {
        return $this->repository->all($perPage, $search);
    }

    public function create(array $data)
    {
        $data['created_by'] = Auth::id();

        return $this->repository->store($data);
    }

    public function update(int $id, array $data)
    {
        $data['updated_by'] = Auth::id();

        return $this->repository->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }
}
