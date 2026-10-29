<?php

namespace Tests\Feature\Api;

use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\PharmacyDispense;
use App\Models\PharmacyInventoryItem;
use App\Models\PharmacyPrescriptionOrder;
use App\Models\Prescription;
use App\Models\User;
use App\Services\Pharmacy\PharmacyAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacyOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_share_an_issued_prescription_with_an_approved_pharmacy(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyOwner);
        $prescription = $this->createPrescription($patientUser);

        $response = $this->actingAs($patientUser, 'api')
            ->postJson($this->shareUrl($prescription), [
                'pharmacy_id' => $pharmacy->id,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'received');

        $this->assertDatabaseHas('pharmacy_prescription_orders', [
            'pharmacy_id'     => $pharmacy->id,
            'prescription_id' => $prescription->id,
            'status'          => 'received',
        ]);
    }

    public function test_patient_pharmacy_directory_lists_only_approved_pharmacies(): void
    {
        $patient = $this->userWithRole('Patient');
        $approvedPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'));
        $this->createPharmacy($this->userWithRole('Pharmacy'), 'pending');

        $this->actingAs($patient, 'api')
            ->getJson('/api/v1/patient/pharmacies')
            ->assertOk()
            ->assertJsonCount(1, 'data.pharmacies')
            ->assertJsonPath('data.pharmacies.0.id', $approvedPharmacy->id)
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonMissingPath('data.pharmacies.0.status');
    }

    public function test_patient_cannot_share_another_patients_prescription_and_receives_404(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $otherPatientUser = $this->userWithRole('Patient');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'));
        $prescription = $this->createPrescription($otherPatientUser);

        $this->actingAs($patientUser, 'api')
            ->postJson($this->shareUrl($prescription), [
                'pharmacy_id' => $pharmacy->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('pharmacy_prescription_orders', [
            'prescription_id' => $prescription->id,
        ]);
    }

    public function test_patient_cannot_share_a_prescription_that_is_not_issued_and_receives_409(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'));
        $prescription = $this->createPrescription($patientUser, 'draft');

        $this->actingAs($patientUser, 'api')
            ->postJson($this->shareUrl($prescription), [
                'pharmacy_id' => $pharmacy->id,
            ])
            ->assertConflict()
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('pharmacy_prescription_orders', [
            'prescription_id' => $prescription->id,
        ]);
    }

    public function test_patient_cannot_share_the_same_prescription_twice_and_receives_409(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'));
        $prescription = $this->createPrescription($patientUser);
        $this->sharePrescription($patientUser, $prescription, $pharmacy);

        $this->actingAs($patientUser, 'api')
            ->postJson($this->shareUrl($prescription), [
                'pharmacy_id' => $pharmacy->id,
            ])
            ->assertConflict()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('pharmacy_prescription_orders', [
            'pharmacy_id'     => $pharmacy->id,
            'prescription_id' => $prescription->id,
        ]);
    }

    public function test_patient_cannot_share_a_prescription_with_an_unapproved_pharmacy(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $unapprovedPharmacy = $this->createPharmacy(
            $this->userWithRole('Pharmacy'),
            'pending',
        );
        $prescription = $this->createPrescription($patientUser);

        $this->actingAs($patientUser, 'api')
            ->postJson($this->shareUrl($prescription), [
                'pharmacy_id' => $unapprovedPharmacy->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('pharmacy_id');

        $this->assertDatabaseMissing('pharmacy_prescription_orders', [
            'prescription_id' => $prescription->id,
        ]);
    }

    public function test_pharmacy_user_cannot_share_a_patients_prescription(): void
    {
        $pharmacyUser = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyUser);
        $patientUser = $this->userWithRole('Patient');
        $prescription = $this->createPrescription($patientUser);

        $this->actingAs($pharmacyUser, 'api')
            ->postJson($this->shareUrl($prescription), [
                'pharmacy_id' => $pharmacy->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('pharmacy_prescription_orders', [
            'prescription_id' => $prescription->id,
        ]);
    }

    public function test_pharmacy_user_cannot_submit_an_arbitrary_prescription_uuid_to_the_queue(): void
    {
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyOwner);
        $prescription = $this->createPrescription($this->userWithRole('Patient'));

        $this->actingAs($pharmacyOwner, 'api')
            ->postJson('/api/v1/pharmacies/prescriptions', [
                'prescription_uuid' => $prescription->uuid,
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('pharmacy_prescription_orders', [
            'prescription_id' => $prescription->id,
            'pharmacy_id'     => $pharmacy->id,
        ]);
    }

    public function test_pharmacy_user_can_view_only_prescriptions_shared_with_their_pharmacy(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $firstPharmacyOwner = $this->userWithRole('Pharmacy');
        $secondPharmacyOwner = $this->userWithRole('Pharmacy');
        $firstPharmacy = $this->createPharmacy($firstPharmacyOwner);
        $secondPharmacy = $this->createPharmacy($secondPharmacyOwner);
        $firstPrescription = $this->createPrescription($patientUser);
        $secondPrescription = $this->createPrescription($patientUser);

        $this->sharePrescription($patientUser, $firstPrescription, $firstPharmacy);
        $this->sharePrescription($patientUser, $secondPrescription, $secondPharmacy);

        $this->actingAs($firstPharmacyOwner, 'api')
            ->getJson('/api/v1/pharmacies/prescriptions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.prescription.uuid', $firstPrescription->uuid);
    }

    public function test_unapproved_pharmacy_cannot_access_prescription_queue(): void
    {
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $this->createPharmacy($pharmacyOwner, 'pending');

        $this->actingAs($pharmacyOwner, 'api')
            ->getJson('/api/v1/pharmacies/prescriptions')
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_pharmacy_user_can_dispense_and_inventory_is_decremented(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyOwner);
        $prescription = $this->createPrescription($patientUser);
        $order = $this->sharePrescription($patientUser, $prescription, $pharmacy);
        $inventoryItem = $this->createInventoryItem($pharmacy, 12, 2);

        $response = $this->actingAs($pharmacyOwner, 'api')
            ->patchJson("/api/v1/pharmacies/prescriptions/{$order->id}/dispense", [
                'items' => [
                    [
                        'inventory_item_id' => $inventoryItem->id,
                        'quantity'          => 4,
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'dispensed');

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $inventoryItem->id,
            'stock_quantity' => 8,
        ]);
        $this->assertDatabaseHas('pharmacy_dispenses', [
            'pharmacy_id'                    => $pharmacy->id,
            'pharmacy_prescription_order_id' => $order->id,
            'pharmacy_inventory_item_id'     => $inventoryItem->id,
            'dispensed_by'                   => $pharmacyOwner->id,
            'quantity'                       => 4,
        ]);
    }

    public function test_pharmacy_user_can_move_a_received_prescription_to_processing(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyOwner);
        $order = $this->sharePrescription(
            $patientUser,
            $this->createPrescription($patientUser),
            $pharmacy,
        );

        $this->actingAs($pharmacyOwner, 'api')
            ->patchJson("/api/v1/pharmacies/prescriptions/{$order->id}/process")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'processing');

        $this->assertDatabaseHas('pharmacy_prescription_orders', [
            'id'     => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_failed_dispensing_rolls_back_all_stock_changes_and_audit_rows(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyOwner);
        $prescription = $this->createPrescription($patientUser);
        $order = $this->sharePrescription($patientUser, $prescription, $pharmacy);
        $availableItem = $this->createInventoryItem($pharmacy, 10, 0);
        $insufficientItem = $this->createInventoryItem($pharmacy, 1, 0);

        $this->actingAs($pharmacyOwner, 'api')
            ->patchJson("/api/v1/pharmacies/prescriptions/{$order->id}/dispense", [
                'items' => [
                    [
                        'inventory_item_id' => $availableItem->id,
                        'quantity'          => 3,
                    ],
                    [
                        'inventory_item_id' => $insufficientItem->id,
                        'quantity'          => 2,
                    ],
                ],
            ])
            ->assertConflict()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $availableItem->id,
            'stock_quantity' => 10,
        ]);
        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'             => $insufficientItem->id,
            'stock_quantity' => 1,
        ]);
        $this->assertDatabaseHas('pharmacy_prescription_orders', [
            'id'     => $order->id,
            'status' => 'received',
        ]);
        $this->assertDatabaseMissing('pharmacy_dispenses', [
            'pharmacy_prescription_order_id' => $order->id,
        ]);
    }

    public function test_pharmacy_user_cannot_update_another_pharmacys_inventory_item(): void
    {
        $firstOwner = $this->userWithRole('Pharmacy');
        $secondOwner = $this->userWithRole('Pharmacy');
        $firstPharmacy = $this->createPharmacy($firstOwner);
        $secondPharmacy = $this->createPharmacy($secondOwner);
        $otherPharmacyItem = $this->createInventoryItem($secondPharmacy, 10, 2);

        $this->actingAs($firstOwner, 'api')
            ->patchJson("/api/v1/pharmacies/inventory/{$otherPharmacyItem->id}", [
                'medicine_name'  => 'Changed by another pharmacy',
                'stock_quantity' => 10,
                'reorder_level'  => 2,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'id'            => $otherPharmacyItem->id,
            'pharmacy_id'   => $secondPharmacy->id,
            'medicine_name' => 'Sample medicine',
        ]);
    }

    public function test_pharmacy_user_can_add_an_inventory_item(): void
    {
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyOwner);

        $response = $this->actingAs($pharmacyOwner, 'api')
            ->postJson('/api/v1/pharmacies/inventory', [
                'medicine_name'  => 'Acetaminophen',
                'generic_name'   => 'Paracetamol',
                'strength'       => '500 mg',
                'stock_quantity' => 60,
                'reorder_level'  => 12,
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.medicine_name', 'Acetaminophen');

        $this->assertDatabaseHas('pharmacy_inventory_items', [
            'pharmacy_id'    => $pharmacy->id,
            'medicine_name'  => 'Acetaminophen',
            'stock_quantity' => 60,
        ]);
    }

    public function test_pharmacy_dashboard_counters_are_scoped_to_the_owner(): void
    {
        $patientUser = $this->userWithRole('Patient');
        $pharmacyOwner = $this->userWithRole('Pharmacy');
        $otherOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($pharmacyOwner);
        $otherPharmacy = $this->createPharmacy($otherOwner);
        $prescription = $this->createPrescription($patientUser);
        $order = $this->sharePrescription($patientUser, $prescription, $pharmacy);
        $otherPrescription = $this->createPrescription($patientUser);
        $this->sharePrescription($patientUser, $otherPrescription, $otherPharmacy);

        $inventoryItem = $this->createInventoryItem($pharmacy, 2, 5, today()->addDays(20)->toDateString());
        $this->createInventoryItem($otherPharmacy, 0, 5, today()->addDays(10)->toDateString());
        PharmacyDispense::query()->create([
            'pharmacy_id'                    => $pharmacy->id,
            'pharmacy_prescription_order_id' => $order->id,
            'pharmacy_inventory_item_id'     => $inventoryItem->id,
            'dispensed_by'                   => $pharmacyOwner->id,
            'quantity'                       => 4,
            'dispensed_at'                   => now(),
        ]);

        $analytics = app(PharmacyAnalyticsService::class)->dashboard($pharmacyOwner);

        $this->assertSame(1, $analytics['operations']['todayPrescriptions']);
        $this->assertSame(1, $analytics['operations']['pendingPrescriptions']);
        $this->assertSame(4, $analytics['operations']['medicinesDispensedToday']);
        $this->assertSame(1, $analytics['operations']['lowStockDrugs']);
        $this->assertSame(1, $analytics['operations']['expiringSoon']);
        $this->assertSame([$inventoryItem->id], array_column(
            $analytics['operations']['lowStockItems'],
            'id',
        ));
        $this->assertSame([$inventoryItem->id], array_column(
            $analytics['operations']['expiringSoonItems'],
            'id',
        ));
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate([
            'name'       => $roleName,
            'guard_name' => 'api',
        ]);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    private function createPharmacy(User $owner, string $status = 'approved'): Pharmacy
    {
        return Pharmacy::query()->create([
            'user_id' => $owner->id,
            'name'    => 'Pharmacy '.$owner->id,
            'slug'    => 'pharmacy-'.$owner->id,
            'status'  => $status,
        ]);
    }

    private function createPrescription(User $patientUser, string $status = 'issued'): Prescription
    {
        $patient = Patient::query()->create([
            'user_id'        => $patientUser->id,
            'patient_number' => 'PAT-'.$patientUser->id.'-'.fake()->unique()->numerify('######'),
            'name'           => $patientUser->name,
            'phone'          => '01700000000',
        ]);

        return Prescription::query()->create([
            'patient_id'          => $patient->id,
            'prescription_number' => 'RX-'.$patientUser->id.'-'.fake()->unique()->numerify('######'),
            'header_left'         => '',
            'header_right'        => '',
            'footer_info'         => '',
            'status'              => $status,
        ]);
    }

    private function createInventoryItem(
        Pharmacy $pharmacy,
        int $stock,
        int $reorderLevel,
        ?string $expiryDate = null,
    ): PharmacyInventoryItem {
        return $pharmacy->inventoryItems()->create([
            'medicine_name'  => 'Sample medicine',
            'stock_quantity' => $stock,
            'reorder_level'  => $reorderLevel,
            'expiry_date'    => $expiryDate,
        ]);
    }

    private function sharePrescription(
        User $patientUser,
        Prescription $prescription,
        Pharmacy $pharmacy,
    ): PharmacyPrescriptionOrder {
        $this->actingAs($patientUser, 'api')
            ->postJson($this->shareUrl($prescription), [
                'pharmacy_id' => $pharmacy->id,
            ])
            ->assertCreated();

        return PharmacyPrescriptionOrder::query()
            ->where('prescription_id', $prescription->id)
            ->firstOrFail();
    }

    private function shareUrl(Prescription $prescription): string
    {
        return "/api/v1/patient/prescriptions/{$prescription->uuid}/pharmacy";
    }
}
