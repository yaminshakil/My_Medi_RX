<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyMenuSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRoleIds = DB::table('roles')
            ->where('name', 'Admin')
            ->pluck('id');

        if ($adminRoleIds->isEmpty()) {
            throw new \RuntimeException('The Admin role must exist before seeding the pharmacy menu.');
        }

        $menu = Menu::query()->updateOrCreate(
            ['slug' => '/admin/pharmacies'],
            [
                'parent_id'   => null,
                'name'        => 'Pharmacies',
                'menu_method' => 'get',
                'menu_icon'   => 'Pill',
                'order_by'    => 8,
            ],
        );

        $menu->roles()->syncWithoutDetaching($adminRoleIds->all());
    }
}
