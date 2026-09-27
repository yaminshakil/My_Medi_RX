<?php

namespace App\Services\Auth;

use App\Interfaces\Auth\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    protected $userRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function paginate($search, $perPage, $role = null): LengthAwarePaginator
    {
        return $this->userRepository->paginate($search, $perPage, $role);
    }

    public function create(array $data): User
    {
        return $this->userRepository->create($data);
    }

    public function update(array $data, $user): int
    {
        return $this->userRepository->update($data, $user);
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function all(): Collection
    {
        return $this->userRepository->all();
    }

    public function find(int $id): ?User
    {
        return $this->userRepository->find($id);
    }
}
