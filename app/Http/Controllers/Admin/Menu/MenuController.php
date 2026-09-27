<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index()
    {
        $menus = $this->menuService->listMenus();

        return Inertia::render('Admin/Menus/Index', [
            'allmenus' => $menus,
            'status'   => session('status'),
        ]);
    }

    public function create()
    {
        $roles = $this->menuService->getAllRoles()->map(fn ($role) => [
            'value' => $role->id,
            'label' => $role->name,
        ]);

        $parentmenus = $this->menuService->getAllParentMenus()->map(fn ($menu) => [
            'value' => $menu->id,
            'label' => $menu->name,
        ]);

        return Inertia::render('Admin/Menus/Create', [
            'roles'       => $roles,
            'parentmenus' => $parentmenus,
            'status'      => session('status'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->menuService->createMenu($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu created successfully');
    }

    public function edit($id)
    {
        $roles = $this->menuService->getAllRoles()->map(fn ($role) => [
            'value' => $role->id,
            'label' => $role->name,
        ]);

        $editmenu = $this->menuService->find($id);
        $parentmenus = $this->menuService->getAllParentMenus()->map(fn ($menu) => [
            'value' => $menu->id,
            'label' => $menu->name,
        ]);

        return Inertia::render('Admin/Menus/Edit', [
            'roles'       => $roles,
            'editmenu'    => $editmenu,
            'role'        => $editmenu->roles->pluck('id')->toArray(),
            'parentmenus' => $parentmenus,
            'status'      => session('status'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->menuService->updateMenu($id, $request->all());

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully');
    }

    public function destroy($id)
    {
        $this->menuService->deleteMenu($id);

        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate(['menus' => 'required|array']);
        $this->menuService->reorderMenus($validated['menus']);

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully');
    }
}
