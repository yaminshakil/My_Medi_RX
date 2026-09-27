<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $map = [];

        // Top Level Menus
        $map['dashboard'] = DB::table('menus')->insertGetId($this->data('Dashboard', '/admin/dashboard', 'LayoutGrid', 0));
        $map['doctors_parent'] = DB::table('menus')->insertGetId($this->data('Doctors', null, 'Stethoscope', 7));
        $map['users'] = DB::table('menus')->insertGetId($this->data('Users', '/admin/users', 'Users', 8));
        $map['roles'] = DB::table('menus')->insertGetId($this->data('Roles', '/admin/roles', 'Settings', 9));
        $map['permissions'] = DB::table('menus')->insertGetId($this->data('Permissions', '/admin/permissions', 'ShieldCheck', 10));
        $map['menus_parent'] = DB::table('menus')->insertGetId($this->data('Menus', null, 'Menu', 13));
        $map['settings'] = DB::table('menus')->insertGetId($this->data('System Settings', '/admin/settings/system', 'Settings', 14));

        // Sub-menus
        $map['menus_list'] = DB::table('menus')->insertGetId($this->data('Menus', '/admin/menus', 'Menu', 0, $map['menus_parent']));
        $map['menus_create'] = DB::table('menus')->insertGetId($this->data('Create Menu', '/admin/menus/create', 'Menu', 1, $map['menus_parent']));
        $map['doctors'] = DB::table('menus')->insertGetId($this->data('Doctors', '/admin/doctors', 'Stethoscope', 0, $map['doctors_parent']));
        $map['doctors_specialties'] = DB::table('menus')->insertGetId($this->data('MedicalSpecialties', '/admin/medical-specialties', 'Stethoscope', 1, $map['doctors_parent']));

        // Role Assignments
        $this->assignRoles($map['dashboard'], [1]);
        $this->assignRoles($map['users'], [1]);
        $this->assignRoles($map['roles'], [1]);
        $this->assignRoles($map['permissions'], [1]);
        $this->assignRoles($map['menus_parent'], [1]);
        $this->assignRoles($map['menus_list'], [1]);
        $this->assignRoles($map['menus_create'], [1]);
        $this->assignRoles($map['settings'], [1]);
        $this->assignRoles($map['doctors_parent'], [1, 2]);
        $this->assignRoles($map['doctors'], [1, 2]);
        $this->assignRoles($map['doctors_specialties'], [1, 2]);
    }

    /**
     * Helper to format menu data
     */
    private function data($name, $slug, $icon, $order, $parentId = null)
    {
        return [
            'parent_id'   => $parentId,
            'name'        => $name,
            'slug'        => $slug,
            'menu_method' => 'get',
            'menu_icon'   => $icon,
            'order_by'    => $order,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }

    /**
     * Helper to assign multiple roles to a menu ID
     */
    private function assignRoles($menuId, array $roleIds)
    {
        foreach ($roleIds as $roleId) {
            DB::table('menu_role')->insert([
                'menu_id'    => $menuId,
                'role_id'    => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
