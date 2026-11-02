<?php

namespace Tests\Feature\BloodBank;

use App\Models\User;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Models\StorageUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StorageUnitControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_storage_units_and_supported_types(): void
    {
        $unit = StorageUnit::query()->create([
            'name' => 'Main refrigerator',
            'type' => 'refrigerator',
        ]);

        $this->actingAs($this->administrator())
            ->get(route('blood-bank.storage-units.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/BloodBank/StorageUnits/Index')
                ->where('storage_units.data.0.id', $unit->id)
                ->where('storage_units.data.0.name', 'Main refrigerator')
                ->where('storage_units.data.0.bags_count', 0)
                ->where('type_options.0.value', 'refrigerator'));
    }

    public function test_admin_can_create_a_storage_unit(): void
    {
        $this->actingAs($this->administrator())
            ->from(route('blood-bank.storage-units.index'))
            ->post(route('blood-bank.storage-units.store'), [
                'name'       => 'Main refrigerator',
                'location'   => 'Ground floor',
                'type'       => 'refrigerator',
                'min_temp_c' => '2.0',
                'max_temp_c' => '6.0',
                'capacity'   => '120',
                'is_active'  => true,
            ])
            ->assertRedirect(route('blood-bank.storage-units.index'))
            ->assertSessionHas('success', 'Storage unit created.');

        $this->assertDatabaseHas('bb_storage_units', [
            'name'       => 'Main refrigerator',
            'location'   => 'Ground floor',
            'type'       => 'refrigerator',
            'min_temp_c' => 2.0,
            'max_temp_c' => 6.0,
            'capacity'   => 120,
            'is_active'  => true,
        ]);
    }

    public function test_admin_can_update_storage_unit_details_and_status(): void
    {
        $unit = StorageUnit::query()->create([
            'name' => 'Old refrigerator',
            'type' => 'refrigerator',
        ]);

        $this->actingAs($this->administrator())
            ->from(route('blood-bank.storage-units.index'))
            ->put(route('blood-bank.storage-units.update', $unit), [
                'name'       => 'Backup freezer',
                'location'   => 'Basement',
                'type'       => 'freezer',
                'min_temp_c' => '-25.0',
                'max_temp_c' => '-18.0',
                'capacity'   => 80,
                'is_active'  => false,
            ])
            ->assertRedirect(route('blood-bank.storage-units.index'))
            ->assertSessionHas('success', 'Storage unit updated.');

        $this->assertDatabaseHas('bb_storage_units', [
            'id'         => $unit->id,
            'name'       => 'Backup freezer',
            'location'   => 'Basement',
            'type'       => 'freezer',
            'min_temp_c' => -25.0,
            'max_temp_c' => -18.0,
            'capacity'   => 80,
            'is_active'  => false,
        ]);
    }

    public function test_storage_unit_rejects_an_invalid_temperature_range(): void
    {
        $this->actingAs($this->administrator())
            ->from(route('blood-bank.storage-units.index'))
            ->post(route('blood-bank.storage-units.store'), [
                'name'       => 'Invalid refrigerator',
                'type'       => 'unknown',
                'min_temp_c' => '6.0',
                'max_temp_c' => '2.0',
                'is_active'  => true,
            ])
            ->assertRedirect(route('blood-bank.storage-units.index'))
            ->assertSessionHasErrors(['type', 'max_temp_c']);

        $this->assertDatabaseMissing('bb_storage_units', [
            'name' => 'Invalid refrigerator',
        ]);
    }

    public function test_admin_can_delete_a_storage_unit_without_blood_bags(): void
    {
        $unit = StorageUnit::query()->create([
            'name' => 'Unused refrigerator',
            'type' => 'refrigerator',
        ]);

        $this->actingAs($this->administrator())
            ->from(route('blood-bank.storage-units.index'))
            ->delete(route('blood-bank.storage-units.destroy', $unit))
            ->assertRedirect(route('blood-bank.storage-units.index'))
            ->assertSessionHas('success', 'Storage unit deleted.');

        $this->assertDatabaseMissing('bb_storage_units', ['id' => $unit->id]);
    }

    public function test_storage_unit_with_blood_bags_cannot_be_deleted(): void
    {
        $unit = StorageUnit::query()->create([
            'name' => 'Used refrigerator',
            'type' => 'refrigerator',
        ]);
        BloodBag::query()->create([
            'bag_number'      => 'BAG-DELETE-001',
            'batch_number'    => 'BATCH-DELETE-001',
            'blood_group'     => 'O+',
            'component'       => 'packed_red_cells',
            'volume_ml'       => 300,
            'collected_at'    => now()->subDay(),
            'expires_at'      => now()->addDays(30),
            'storage_unit_id' => $unit->id,
            'status'          => 'available',
        ]);

        $this->actingAs($this->administrator())
            ->from(route('blood-bank.storage-units.index'))
            ->delete(route('blood-bank.storage-units.destroy', $unit))
            ->assertRedirect(route('blood-bank.storage-units.index'))
            ->assertSessionHas('error', 'Storage units with blood bags cannot be deleted. Deactivate the unit instead.');

        $this->assertModelExists($unit);
    }

    public function test_user_without_blood_bank_access_cannot_manage_storage_units(): void
    {
        $this->actingAs(User::factory()->create([
            'first_name' => 'Regular',
            'last_name'  => 'User',
        ]))
            ->get(route('blood-bank.storage-units.index'))
            ->assertForbidden();
    }

    private function administrator(): User
    {
        $role = Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create([
            'first_name' => 'Blood',
            'last_name'  => 'Bank',
        ]);
        $user->assignRole($role);

        return $user;
    }
}
