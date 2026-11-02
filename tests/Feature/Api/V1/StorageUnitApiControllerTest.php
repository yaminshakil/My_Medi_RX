<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Modules\BloodBank\Models\StorageUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StorageUnitApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_401_when_storage_units_are_requested_without_authentication(): void
    {
        $this->getJson('/api/v1/blood-bank/storage-units')
            ->assertUnauthorized();
    }

    public function test_api_returns_all_storage_units_including_inactive_units(): void
    {
        $role = Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'api',
        ]);
        $user = User::factory()->create([
            'first_name' => 'Blood',
            'last_name'  => 'Bank',
        ]);
        $user->assignRole($role);
        StorageUnit::query()->create([
            'name'       => 'Active refrigerator',
            'location'   => 'Ground floor',
            'type'       => 'refrigerator',
            'min_temp_c' => 2.0,
            'max_temp_c' => 6.0,
            'capacity'   => 120,
            'is_active'  => true,
        ]);
        StorageUnit::query()->create([
            'name'      => 'Inactive freezer',
            'type'      => 'freezer',
            'is_active' => false,
        ]);

        $this->actingAs($user, 'api')
            ->getJson('/api/v1/blood-bank/storage-units')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Active refrigerator')
            ->assertJsonPath('data.0.location', 'Ground floor')
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('data.1.name', 'Inactive freezer')
            ->assertJsonPath('data.1.is_active', false);
    }
}
