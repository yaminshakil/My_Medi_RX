<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanMenuSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = DB::table('roles')
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($adminRole === null) {
            throw new \RuntimeException('The Admin role must exist before seeding subscription plan management.');
        }

        $menu = Menu::query()->updateOrCreate(
            ['slug' => '/admin/subscription-plans'],
            [
                'parent_id'   => null,
                'name'        => 'Subscription Plans',
                'menu_method' => 'get',
                'menu_icon'   => 'CreditCard',
                'order_by'    => 15,
            ],
        );

        $menu->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
