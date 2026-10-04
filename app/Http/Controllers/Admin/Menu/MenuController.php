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
        $rules = [
            'name' => 'required|string|max:255',
            'translations' => 'nullable|array',
            'translations.name' => 'nullable|array',
        ];

        foreach (array_keys(config('languages.supported')) as $locale) {
            $rules["translations.name.{$locale}"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $this->menuService->createMenu($request->all());

        return redirect()->route('menus.index')->with('success', 'Menu created successfully');
    }

    public function edit($id)
    {
        $roles = $this->menuService->getAllRoles()->map(fn ($role) => [
            'value' => $role->id,
            'label' => $role->name,
        ]);

        $menu = $this->menuService->find($id);
        $parentmenus = $this->menuService->getAllParentMenus()->map(fn ($menu) => [
            'value' => $menu->id,
            'label' => $menu->name,
        ]);

        return Inertia::render('Admin/Menus/Edit', [
            'roles'       => $roles,
            'editmenu' => [
            'id' => $menu->id,
            'name' => $menu->name,
            'slug' => $menu->slug,
            'order_by' => $menu->order_by,
            'menu_method' => $menu->menu_method,
            'menu_icon' => $menu->menu_icon,
            'parent_id' => $menu->parent_id,
            'translations' => [
                'name' => $menu->translations
                    ->where('field', 'name')
                    ->pluck('value', 'locale')
                    ->toArray(),
                ],
            ],
            'role'        => $menu->roles->pluck('id')->toArray(),
            'parentmenus' => $parentmenus,
            'status'      => session('status'),

        ]);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'translations' => 'nullable|array',
            'translations.name' => 'nullable|array',
        ];

        foreach (array_keys(config('languages.supported')) as $locale) {
            $rules["translations.name.{$locale}"] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

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
