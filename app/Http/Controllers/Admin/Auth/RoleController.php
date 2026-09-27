<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Auth\RoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10); // default 10
        $perPage = $request->input('per_page', $recordsPerPage);
        $search = null;

        if ($request->filled('search')) {
            $search = $request->search;
        }

        $roles = $this->roleService->all($search, $perPage);

        return Inertia::render('Admin/Roles/index', [
            'roles'   => $roles,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Roles/create', [
            'permissions' => Permission::pluck('name'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required',
            'permissions' => 'required',
        ]);

        $role = $this->roleService->create($data);

        return redirect()->route('roles.index')->with(['success' => 'Role Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = $this->roleService->find($id);

        return Inertia::render('Admin/Roles/edit', [
            'role'            => $role,
            'rolePermissions' => $role->permissions->pluck('name'),
            'permissions'     => Permission::pluck('name'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name'        => 'required',
            'permissions' => 'required',
        ]);

        $role = $this->roleService->update($data, $id);

        return redirect()->route('roles.index')->with(['success' => 'Role Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = $this->roleService->delete($id);

        return redirect()->route('roles.index')->with(['success' => 'Role Deleted Successfully']);
    }
}
