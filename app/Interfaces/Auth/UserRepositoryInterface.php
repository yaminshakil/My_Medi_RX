<?php

namespace App\Interfaces\Auth;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function all(): Collection;

    public function paginate($search, $perPage): LengthAwarePaginator;

    public function create(array $data): ?User;

    public function update(array $data, $user): int;

    public function delete(int $id): bool;

    public function find(int $id): ?User;
}
