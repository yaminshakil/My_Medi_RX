<?php

namespace Tests\Feature\Admin;

use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\PharmacyDispense;
use App\Models\PharmacyInventoryItem;
use App\Models\PharmacyPrescriptionOrder;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacyInventoryItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_inventory_across_pharmacies_and_view_stock_metrics(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');
        $otherPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Health Point');
        PharmacyInventoryItem::query()->create([
            'pharmacy_id'    => $pharmacy->id,
            'medicine_name'  => 'Low stock medicine',
            'stock_quantity' => 3,
            'reorder_level'  => 5,
            'expiry_date'    => today()->addDays(10),
        ]);
        PharmacyInventoryItem::query()->create([
            'pharmacy_id'    => $otherPharmacy->id,
            'medicine_name'  => 'Healthy stock medicine',
            'stock_quantity' => 20,
            'reorder_level'  => 5,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.inventory.index', ['stock_status' => 'low']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Inventory/Index')
                ->where('inventory.total', 1)
                ->where('inventory.data.0.medicine_name', 'Low stock medicine')
                ->where('inventory.data.0.pharmacy.name', 'Central Care')
                ->where('inventory.data.0.expiry_date', today()->addDays(10)->toDateString())
                ->where('stats.totalItems', 2)
                ->where('stats.totalUnits', 23)
                ->where('stats.lowStockItems', 1)
                ->where('stats.expiringSoonItems', 1)
                ->where('filters.stock_status', 'low'));
    }

    public function test_pharmacy_user_only_sees_their_own_inventory_and_metrics(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyUser, 'Central Care');
        $otherPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Health Point');
        $this->createInventoryItem($pharmacy, [
            'medicine_name'  => 'Owned medicine',
            'stock_quantity' => 7,
        ]);
        $this->createInventoryItem($otherPharmacy, [
            'medicine_name'  => 'Other pharmacy medicine',
            'stock_quantity' => 0,
            'reorder_level'  => 2,
            'expiry_date'    => today()->subDay(),
        ]);
        $this->createInventoryItem($otherPharmacy, [
            'medicine_name'  => 'Other expiring medicine',
            'stock_quantity' => 3,
            'reorder_level'  => 4,
            'expiry_date'    => today()->addDays(10),
        ]);

        $this->actingAs($pharmacyUser)
            ->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Inventory/Index')
                ->where('isAdmin', false)
                ->where('inventory.total', 1)
                ->where('inventory.data.0.medicine_name', 'Owned medicine')
                ->where('stats.totalItems', 1)
                ->where('stats.totalUnits', 7)
                ->where('stats.lowStockItems', 0)
                ->where('stats.outOfStockItems', 0)
                ->where('stats.expiringSoonItems', 0)
                ->where('stats.expiredItems', 0)
                ->where('pharmacies', [
                    ['id' => $pharmacy->id, 'name' => 'Central Care'],
                ]));
    }

    public function test_pharmacy_user_cannot_filter_inventory_by_another_pharmacy(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $this->createPharmacy($pharmacyUser, 'Central Care');
        $otherPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Health Point');

        $this->actingAs($pharmacyUser)
            ->get(route('admin.inventory.index', ['pharmacy_id' => $otherPharmacy->id]))
            ->assertRedirect()
            ->assertSessionHasErrors('pharmacy_id');
    }

    public function test_pharmacy_user_can_create_inventory_for_their_own_pharmacy(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyUser, 'Central Care');

        $this->actingAs($pharmacyUser)
            ->post(route('admin.inventory.store'), [
                'medicine_name'       => 'Paracetamol',
                'generic_name'        => 'Acetaminophen',
                'strength'            => '500 mg',
                'regular_price'       => '15.00',
                'discount_percentage' => '12.50',
                'stock_quantity'      => 25,
                'reorder_level'       => 8,
                'expiry_date'         => today()->addMonths(6)->toDateString(),
            ])
            ->assertRedirect(route('admin.inventory.index'));

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'pharmacy_id'         => $pharmacy->id,
            'medicine_name'       => 'Paracetamol',
            'stock_quantity'      => 25,
            'regular_price'       => '15.00',
            'discount_percentage' => '12.50',
        ]);
    }

    public function test_pharmacy_csv_import_replaces_matching_stock_and_exports_only_its_own_items(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyUser, 'Central Care');
        $otherPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Health Point');
        $existingItem = $this->createInventoryItem($pharmacy, [
            'medicine_name'  => 'Paracetamol',
            'generic_name'   => 'Acetaminophen',
            'strength'       => '500 mg',
            'stock_quantity' => 9,
        ]);
        $this->createInventoryItem($otherPharmacy, [
            'medicine_name' => 'Other pharmacy medicine',
        ]);
        $csv = implode("\n", [
            'pharmacy_id,medicine_name,generic_name,strength,regular_price,discount_percentage,stock_quantity,reorder_level,expiry_date',
            "{$pharmacy->id},Paracetamol,Acetaminophen,500 mg,12.50,10,3,2,",
            "{$pharmacy->id},Ibuprofen,,200 mg,8.00,0,20,5,",
            "{$pharmacy->id},=1+1,,500 mg,8.00,0,1,1,",
        ]);

        $this->actingAs($pharmacyUser)
            ->post(route('admin.inventory.import'), [
                'file' => UploadedFile::fake()->createWithContent('inventory.csv', $csv),
            ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHas('success', 'Inventory imported: 2 item(s) created and 1 updated. Existing quantities were replaced with CSV quantities.');

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'                  => $existingItem->id,
            'stock_quantity'      => 3,
            'regular_price'       => '12.50',
            'discount_percentage' => '10.00',
        ]);
        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'pharmacy_id'    => $pharmacy->id,
            'medicine_name'  => 'Ibuprofen',
            'stock_quantity' => 20,
            'regular_price'  => '8.00',
        ]);
        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'pharmacy_id'   => $pharmacy->id,
            'medicine_name' => '=1+1',
        ]);

        $export = $this->actingAs($pharmacyUser)
            ->get(route('admin.inventory.export'))
            ->assertOk()
            ->assertDownload('pharmacy-inventory.csv')
            ->streamedContent();

        $this->assertStringContainsString('Paracetamol', $export);
        $this->assertStringContainsString("'=1+1", $export);
        $this->assertStringNotContainsString('Other pharmacy medicine', $export);
    }

    public function test_invalid_inventory_csv_does_not_import_any_rows(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyUser, 'Central Care');
        $csv = implode("\n", [
            'pharmacy_id,medicine_name,generic_name,strength,regular_price,discount_percentage,stock_quantity,reorder_level,expiry_date',
            "{$pharmacy->id},Valid medicine,,10 mg,12.50,5,3,2,",
            "{$pharmacy->id},Invalid medicine,,10 mg,12.50,101,3,2,",
        ]);

        $this->actingAs($pharmacyUser)
            ->from(route('admin.inventory.index'))
            ->post(route('admin.inventory.import'), [
                'file' => UploadedFile::fake()->createWithContent('inventory.csv', $csv),
            ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('pharmacy_inventory_items', [
            'pharmacy_id'   => $pharmacy->id,
            'medicine_name' => 'Valid medicine',
        ]);
        $this->assertDatabaseMissing('pharmacy_inventory_items', [
            'pharmacy_id'   => $pharmacy->id,
            'medicine_name' => 'Invalid medicine',
        ]);
    }

    public function test_pharmacy_csv_import_rejects_rows_for_another_pharmacy(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $this->createPharmacy($pharmacyUser, 'Central Care');
        $otherPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Health Point');
        $csv = implode("\n", [
            'pharmacy_id,medicine_name,generic_name,strength,regular_price,discount_percentage,stock_quantity,reorder_level,expiry_date',
            "{$otherPharmacy->id},Unauthorized medicine,,10 mg,12.50,0,3,2,",
        ]);

        $this->actingAs($pharmacyUser)
            ->from(route('admin.inventory.index'))
            ->post(route('admin.inventory.import'), [
                'file' => UploadedFile::fake()->createWithContent('inventory.csv', $csv),
            ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('pharmacy_inventory_items', [
            'pharmacy_id'   => $otherPharmacy->id,
            'medicine_name' => 'Unauthorized medicine',
        ]);
    }

    public function test_pharmacy_user_cannot_create_inventory_for_another_pharmacy(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $this->createPharmacy($pharmacyUser, 'Central Care');
        $otherPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Health Point');

        $this->actingAs($pharmacyUser)
            ->from(route('admin.inventory.index'))
            ->post(route('admin.inventory.store'), [
                'pharmacy_id'    => $otherPharmacy->id,
                'medicine_name'  => 'Paracetamol',
                'stock_quantity' => 25,
                'reorder_level'  => 8,
            ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHasErrors('pharmacy_id');

        $this->assertDatabaseMissing('pharmacy_inventory_items', [
            'pharmacy_id'   => $otherPharmacy->id,
            'medicine_name' => 'Paracetamol',
        ]);
    }

    public function test_pharmacy_user_can_update_and_adjust_their_own_inventory(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyUser, 'Central Care');
        $item = $this->createInventoryItem($pharmacy, ['stock_quantity' => 10]);

        $this->actingAs($pharmacyUser)
            ->put(route('admin.inventory.update', $item), [
                'medicine_name' => 'Updated medicine',
                'reorder_level' => 4,
            ])
            ->assertRedirect(route('admin.inventory.index'));

        $this->actingAs($pharmacyUser)
            ->patch(route('admin.inventory.stock', $item), [
                'direction' => 'increase',
                'quantity'  => 5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'pharmacy_id'    => $pharmacy->id,
            'medicine_name'  => 'Updated medicine',
            'stock_quantity' => 15,
        ]);
    }

    public function test_pharmacy_user_cannot_change_or_delete_another_pharmacys_inventory(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $this->createPharmacy($pharmacyUser, 'Central Care');
        $otherPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Health Point');
        $item = $this->createInventoryItem($otherPharmacy);

        $this->actingAs($pharmacyUser)
            ->put(route('admin.inventory.update', $item), [
                'medicine_name' => 'Tampered medicine',
                'reorder_level' => 0,
            ])
            ->assertForbidden();

        $this->actingAs($pharmacyUser)
            ->patch(route('admin.inventory.stock', $item), [
                'direction' => 'increase',
                'quantity'  => 1,
            ])
            ->assertForbidden();

        $this->actingAs($pharmacyUser)
            ->delete(route('admin.inventory.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'pharmacy_id'    => $otherPharmacy->id,
            'medicine_name'  => 'Sample medicine',
            'stock_quantity' => 10,
        ]);
    }

    public function test_admin_can_create_an_inventory_item_with_initial_stock(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');

        $this->actingAs($admin)
            ->post(route('admin.inventory.store'), [
                'pharmacy_id'    => $pharmacy->id,
                'medicine_name'  => 'Paracetamol',
                'generic_name'   => 'Acetaminophen',
                'strength'       => '500 mg',
                'stock_quantity' => 25,
                'reorder_level'  => 8,
                'expiry_date'    => today()->addMonths(6)->toDateString(),
            ])
            ->assertRedirect(route('admin.inventory.index'));

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'pharmacy_id'    => $pharmacy->id,
            'medicine_name'  => 'Paracetamol',
            'stock_quantity' => 25,
            'reorder_level'  => 8,
        ]);
    }

    public function test_admin_can_update_medicine_details_without_changing_stock_or_pharmacy(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');
        $item = $this->createInventoryItem($pharmacy, [
            'medicine_name'  => 'Old name',
            'stock_quantity' => 12,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.inventory.update', $item), [
                'medicine_name' => 'Updated name',
                'generic_name'  => 'Updated generic',
                'strength'      => '250 mg',
                'reorder_level' => 4,
                'expiry_date'   => null,
            ])
            ->assertRedirect(route('admin.inventory.index'));

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'pharmacy_id'    => $pharmacy->id,
            'medicine_name'  => 'Updated name',
            'stock_quantity' => 12,
        ]);
    }

    public function test_admin_cannot_change_stock_quantity_through_the_item_update_form(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');
        $item = $this->createInventoryItem($pharmacy, ['stock_quantity' => 12]);

        $this->actingAs($admin)
            ->put(route('admin.inventory.update', $item), [
                'medicine_name'  => $item->medicine_name,
                'reorder_level'  => $item->reorder_level,
                'stock_quantity' => 100,
            ])
            ->assertSessionHasErrors(['stock_quantity']);

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'stock_quantity' => 12,
        ]);
    }

    public function test_admin_can_increase_and_decrease_inventory_stock(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');
        $item = $this->createInventoryItem($pharmacy, ['stock_quantity' => 10]);

        $this->actingAs($admin)
            ->patch(route('admin.inventory.stock', $item), [
                'direction' => 'increase',
                'quantity'  => 5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'stock_quantity' => 15,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.inventory.stock', $item), [
                'direction' => 'decrease',
                'quantity'  => 3,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'stock_quantity' => 12,
        ]);
    }

    public function test_stock_adjustment_cannot_decrease_below_zero(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');
        $item = $this->createInventoryItem($pharmacy, ['stock_quantity' => 2]);

        $this->actingAs($admin)
            ->from(route('admin.inventory.index'))
            ->patch(route('admin.inventory.stock', $item), [
                'direction' => 'decrease',
                'quantity'  => 3,
            ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHasErrors([
                'quantity' => 'The decrease quantity cannot exceed the available stock.',
            ]);

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'stock_quantity' => 2,
        ]);
    }

    public function test_stock_adjustment_cannot_exceed_the_supported_maximum(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');
        $item = $this->createInventoryItem($pharmacy, ['stock_quantity' => 10000000]);

        $this->actingAs($admin)
            ->from(route('admin.inventory.index'))
            ->patch(route('admin.inventory.stock', $item), [
                'direction' => 'increase',
                'quantity'  => 1,
            ])
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHasErrors([
                'quantity' => 'The adjusted stock cannot exceed 10,000,000 units.',
            ]);

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $item->id,
            'stock_quantity' => 10000000,
        ]);
    }

    public function test_admin_can_delete_inventory_without_dispense_history(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'Central Care');
        $item = $this->createInventoryItem($pharmacy);

        $this->actingAs($admin)
            ->delete(route('admin.inventory.destroy', $item))
            ->assertRedirect(route('admin.inventory.index'));

        $this->assertDatabaseMissing('pharmacy_inventory_items', ['id' => $item->id]);
    }

    public function test_admin_cannot_delete_inventory_with_dispense_history(): void
    {
        $admin = $this->userWithRole('Admin');
        $owner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($owner, 'Central Care');
        $patientUser = $this->userWithRole('Patient');
        $patient = Patient::query()->create([
            'user_id'        => $patientUser->id,
            'patient_number' => 'PAT-'.$patientUser->id,
            'name'           => $patientUser->name,
            'phone'          => '01700000000',
        ]);
        $prescription = Prescription::query()->create([
            'patient_id'          => $patient->id,
            'prescription_number' => 'RX-'.$patientUser->id,
            'header_left'         => '',
            'header_right'        => '',
            'footer_info'         => '',
            'status'              => 'issued',
        ]);
        $order = PharmacyPrescriptionOrder::query()->create([
            'pharmacy_id'     => $pharmacy->id,
            'prescription_id' => $prescription->id,
            'status'          => 'received',
            'received_at'     => now(),
        ]);
        $item = $this->createInventoryItem($pharmacy);
        PharmacyDispense::query()->create([
            'pharmacy_id'                    => $pharmacy->id,
            'pharmacy_prescription_order_id' => $order->id,
            'pharmacy_inventory_item_id'     => $item->id,
            'dispensed_by'                   => $owner->id,
            'quantity'                       => 1,
            'dispensed_at'                   => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.inventory.index'))
            ->delete(route('admin.inventory.destroy', $item))
            ->assertRedirect(route('admin.inventory.index'))
            ->assertSessionHasErrors([
                'inventoryItem' => 'This item cannot be deleted because it has dispense history.',
            ]);

        $this->assertDatabaseHas('pharmacy_inventory_items', ['id' => $item->id]);
    }

    private function userWithRole(string $roleName): User
    {
        Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'web',
        ]);
        $role = Role::firstOrCreate([
            'name'       => $roleName,
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name'  => 'User',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createPharmacy(User $owner, string $name): Pharmacy
    {
        return Pharmacy::query()->create([
            'user_id' => $owner->id,
            'name'    => $name,
            'slug'    => str($name)->slug().'-'.$owner->id,
            'status'  => 'approved',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createInventoryItem(Pharmacy $pharmacy, array $attributes = []): PharmacyInventoryItem
    {
        return $pharmacy->inventoryItems()->create(array_merge([
            'medicine_name'  => 'Sample medicine',
            'stock_quantity' => 10,
            'reorder_level'  => 3,
        ], $attributes));
    }
}
