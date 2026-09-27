<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Auth\PermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
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

        $permissions = $this->permissionService->all($search, $perPage);

        return Inertia::render('Admin/Permissions/index', [
            'permissions' => $permissions,
            'filters'     => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Permissions/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        $permission = $this->permissionService->create($data);

        return redirect()->route('permissions.index')->with(['success' => 'Permission Created Successfully']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $permission = $this->permissionService->find($id);

        return Inertia::render('Admin/Permissions/edit', [
            'permission' => $permission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        $permission = $this->permissionService->update($data, $id);

        return redirect()->route('permissions.index')->with(['success' => 'Permission Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permission = $this->permissionService->delete($id);

        return redirect()->route('permissions.index')->with(['success' => 'Permission Deleted Successfully']);
    }
}
