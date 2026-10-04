<?php

namespace App\Repositories;

use App\Interfaces\MenuRepositoryInterface;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class MenuRepository implements MenuRepositoryInterface
{
    public function getAllParentMenus()
    {
        $locale = app()->getLocale();
        $defaultLocale = config('languages.default', 'en');

        $locales = array_unique([$locale, $defaultLocale]);

        return Menu::with([
            'translations' => function ($query) use ($locales) {
                $query->where('field', 'name')
                    ->whereIn('locale', $locales);
            },
            'childmenus.translations' => function ($query) use ($locales) {
                $query->where('field', 'name')
                    ->whereIn('locale', $locales);
            },
        ])
        ->whereNull('parent_id')
        ->orderBy('order_by', 'ASC')
        ->get();
    }

    public function getAllRoles()
    {
        return Role::all();
    }

    public function create(array $data)
    {
        $translations = $data['translations'] ?? [];

        unset($data['translations']);

        $menu = Menu::create($data);

        foreach ($translations as $field => $locales) {
            $menu->setTranslations($field, $locales);
        }

        return $menu;
    }

    public function find($id)
    {
        $locale = app()->getLocale();
        $defaultLocale = config('languages.default', 'en');

        return Menu::with([
            'translations' => function ($query) use ($locale, $defaultLocale) {
                $query->where('field', 'name')
                    ->whereIn('locale', array_unique([
                        $locale,
                        $defaultLocale,
                    ]));
            },
        ])->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $translations = $data['translations'] ?? [];

        unset($data['translations']);
        $menu = $this->find($id);
        $menu->update($data);

        foreach ($translations as $field => $locales) {
            $menu->setTranslations($field, $locales);
        }

        return $menu->refresh();
    }

    public function delete($id)
    {
        return Menu::findOrFail($id)->delete();
    }

    public function updateOrder(array $menus)
    {
        DB::transaction(function () use ($menus) {
            $this->saveMenuOrder($menus);
        });
    }

    private function saveMenuOrder($menus, $parentId = null)
    {
        foreach ($menus as $index => $menu) {
            Menu::where('id', $menu['id'])->update(['order_by' => $index]);

            if (isset($menu['submenu']) && ! empty($menu['submenu'])) {
                $this->saveMenuOrder($menu['submenu'], $menu['id']);
            }
        }
    }
}
