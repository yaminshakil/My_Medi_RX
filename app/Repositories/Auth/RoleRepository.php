<?php

namespace App\Repositories\Auth;

use App\Interfaces\Auth\RoleRepositoryInterface;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function all($search, $perPage)
    {
        $roles = Role::query();

        if ($search) {
            $roles->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
            );
        }

        $roles = $roles->latest()->with('permissions')->paginate($perPage)->withQueryString();

        $roles->getCollection()->transform(fn ($role) => [
            'id'          => $role->id,
            'name'        => $role->name,
            'permissions' => $role->permissions,
            'created_at'  => $role->created_at->format('d M Y'),
        ]);

        return $roles;
    }

    public function create(array $data): ?Role
    {
        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions']);

        return $role;
    }

    public function update(array $data, int $id): int
    {
        $role = Role::findOrFail($id);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions']);

        return $id;
    }

    public function delete(int $id): bool
    {
        $role = Role::findOrFail($id);

        return $role->delete();
    }

    public function find(int $id): ?Role
    {
        return Role::find($id);
    }
}
