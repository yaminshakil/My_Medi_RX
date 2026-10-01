<?php

namespace App\Services\Eprescriptions;

use App\Interfaces\Eprescriptions\MedicineDurationRepositoryInterface;

class MedicineDurationService
{
    protected $repository;

    public function __construct(MedicineDurationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function paginate($search, $perPage = 15)
    {
        return $this->repository->paginate($search, $perPage);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function store(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
