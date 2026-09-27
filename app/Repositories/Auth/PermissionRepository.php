<?php

namespace App\Repositories\Auth;

use App\Interfaces\Auth\PermissionRepositoryInterface;
use Spatie\Permission\Models\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function all($search, $perPage)
    {
        $permissions = Permission::query();

        if ($search) {
            $permissions->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
            );
        }

        $permissions = $permissions->latest()->with('permissions')->paginate($perPage)->withQueryString();

        $permissions->getCollection()->transform(fn ($permission) => [
            'id'         => $permission->id,
            'name'       => $permission->name,
            'created_at' => $permission->created_at->format('d M Y'),
        ]);

        return $permissions;
    }

    public function create(array $data): ?Permission
    {
        $permission = Permission::create(['name' => $data['name']]);

        return $permission;
    }

    public function update(array $data, int $id): int
    {
        $permission = Permission::findOrFail($id);

        $permission->update(['name' => $data['name']]);

        return $id;
    }

    public function delete(int $id): bool
    {
        $permission = Permission::findOrFail($id);

        return $permission->delete();
    }

    public function find(int $id): ?Permission
    {
        return Permission::find($id);
    }
}
