<?php

namespace App\Services;

use App\Interfaces\MenuRepositoryInterface;
use App\Models\Setting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class MenuService
{
    protected $menuRepository;

    public function __construct(MenuRepositoryInterface $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    public function listMenus()
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);

        $items = $this->menuRepository->getAllParentMenus();

        $menus = new LengthAwarePaginator(
            $items,
            $items->count(),
            $items->count() ?: 1,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $menus->getCollection()->transform(fn ($menu) => [
            'id'          => $menu->id,
            'name'        => $menu->translated_name,
            'translations' => $menu->translations
            ->groupBy('field')
            ->map(fn ($items) => $items->pluck('value', 'locale')),
            'slug'        => $menu->slug,
            'order_by'    => $menu->order_by,
            'menu_method' => $menu->menu_method,
            'menu_icon'   => $menu->menu_icon,
            'is_admin'    => Auth::user()->hasRole('admin'),
            'submenu'     => $menu->childmenus->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->translated_name,
                    'slug' => $child->slug,
                    'translations' => [
                        'name' => $child->translations
                            ->pluck('value', 'locale')
                            ->toArray(),
                    ],
                ];
            }),
        ]);

        return $menus;
    }

    public function createMenu(array $data)
    {
        $menu = $this->menuRepository->create($data);

        if (! empty($data['role'])) {
            $menu->roles()->sync($data['role']);
        }

        return $menu;
    }

    public function updateMenu($id, array $data)
    {
        $menu = $this->menuRepository->update($id, $data);

        $menu->roles()->sync($data['role'] ?? []);

        return $menu;
    }

    public function deleteMenu($id)
    {
        return $this->menuRepository->delete($id);
    }

    public function reorderMenus(array $menus)
    {
        return $this->menuRepository->updateOrder($menus);
    }

    public function getAllRoles()
    {
        return $this->menuRepository->getAllRoles();
    }

    public function getAllParentMenus()
    {
        return $this->menuRepository->getAllParentMenus();
    }

    public function find($id)
    {
        return $this->menuRepository->find($id);
    }
}
