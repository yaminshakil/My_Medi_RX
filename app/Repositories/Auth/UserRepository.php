<?php

namespace App\Repositories\Auth;

use App\Interfaces\Auth\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    public function all(): Collection
    {
        return User::all();
    }

    public function paginate($search, $perPage, $role = null): LengthAwarePaginator
    {
        $users = User::query();

        if ($search) {
            $users->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            );
        }

        if ($role) {
            $users->whereHas('roles', fn ($query) => $query->where('name', $role));
        }

        $users = $users->latest()->with('roles')->paginate($perPage)->withQueryString();
        $users->getCollection()->transform(fn ($user) => [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'roles'      => $user->roles,
            'created_at' => $user->created_at->format('d M Y'),
        ]);

        return $users;
    }

    public function create(array $data): ?User
    {
        $user = User::create($this->nameParts($data) + $data + [
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['roles']);

        return $user;
    }

    public function update(array $data, $user): int
    {
        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ] + $this->nameParts($data);

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $updated = $user->update($updateData);

        $user->syncRoles($data['roles']);

        return $updated;
    }

    public function delete(int $id): bool
    {
        $user = User::findOrFail($id);

        return $user->delete();
    }

    public function find(int $id): ?User
    {
        return User::findOrFail($id);
    }

    private function nameParts(array $data): array
    {
        if (! empty($data['first_name']) || ! empty($data['last_name'])) {
            return [
                'first_name' => $data['first_name'] ?? '',
                'last_name'  => $data['last_name'] ?? '',
            ];
        }

        $parts = preg_split('/\s+/', trim($data['name'] ?? ''), 2);

        return [
            'first_name' => $parts[0] ?? '',
            'last_name'  => $parts[1] ?? '',
        ];
    }
}
