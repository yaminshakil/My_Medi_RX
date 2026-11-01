<?php

namespace Tests\Feature\Api\V1;

use App\Models\Hospital;
use App\Models\User;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\Patient;
use App\Modules\BloodBank\Models\Physician;
use App\Modules\BloodBank\Models\StorageUnit;
use App\Modules\BloodBank\Notifications\EmergencyBloodRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BloodBankApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_blood_bank_api_request_is_unauthenticated(): void
    {
        $this->getJson('/api/v1/blood-bank/dashboard')
            ->assertUnauthorized();
    }

    public function test_returns_403_when_user_lacks_blood_bank_access(): void
    {
        $this->actingAs(User::factory()->create([
            'first_name' => 'Regular',
            'last_name'  => 'User',
        ]), 'api')
            ->getJson('/api/v1/blood-bank/dashboard')
            ->assertForbidden();
    }

    public function test_authorized_user_can_register_and_list_blood_bags(): void
    {
        $user = $this->administrator();
        $storageUnit = StorageUnit::create(['name' => 'Fridge A', 'is_active' => true]);

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/blood-bank/bags', [
                'bag_number'      => 'BAG-API-001',
                'batch_number'    => 'BATCH-API-001',
                'blood_group'     => 'O+',
                'component'       => 'packed_red_cells',
                'volume_ml'       => 300,
                'collected_at'    => now()->subDay()->toDateTimeString(),
                'storage_unit_id' => $storageUnit->id,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.bag_number', 'BAG-API-001')
            ->assertJsonPath('data.status', 'available');

        $this->assertDatabaseHas('bb_blood_bags', [
            'bag_number'      => 'BAG-API-001',
            'registered_by'   => $user->id,
            'storage_unit_id' => $storageUnit->id,
            'status'          => 'available',
        ]);

        $this->getJson('/api/v1/blood-bank/bags?blood_group=O%2B&per_page=5')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.bag_number', 'BAG-API-001');
    }

    public function test_returns_422_when_blood_bag_uses_inactive_storage_unit(): void
    {
        $this->actingAs($this->administrator(), 'api');
        $storageUnit = StorageUnit::create(['name' => 'Inactive fridge', 'is_active' => false]);

        $this->postJson('/api/v1/blood-bank/bags', [
            'bag_number'      => 'BAG-API-INACTIVE',
            'batch_number'    => 'BATCH-API-002',
            'blood_group'     => 'A+',
            'component'       => 'whole_blood',
            'volume_ml'       => 450,
            'collected_at'    => now()->subDay()->toDateTimeString(),
            'storage_unit_id' => $storageUnit->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('storage_unit_id');

        $this->assertDatabaseMissing('bb_blood_bags', ['bag_number' => 'BAG-API-INACTIVE']);
    }

    public function test_authorized_user_can_issue_a_bag_and_record_the_transfusion_outcome(): void
    {
        $this->travelTo('2026-11-01 09:00:00');

        $user = $this->administrator();
        $hospital = $this->hospitalFor($user);
        $storageUnit = StorageUnit::create(['name' => 'Issue fridge', 'is_active' => true]);
        $bag = $this->availableBag($storageUnit, 'BAG-ISSUE-001');
        $patient = Patient::create([
            'mrn'         => 'MRN-API-001',
            'name'        => 'API Patient',
            'blood_group' => 'O+',
        ]);
        $physician = Physician::create(['name' => 'Dr API', 'is_active' => true]);
        $this->actingAs($user, 'api');

        $this->getJson('/api/v1/blood-bank/lookups')
            ->assertOk()
            ->assertJsonPath('data.storage_units.0.id', $storageUnit->id)
            ->assertJsonPath('data.physicians.0.id', $physician->id);

        $this->getJson('/api/v1/blood-bank/patients?search=MRN-API-001')
            ->assertOk()
            ->assertJsonPath('data.0.mrn', 'MRN-API-001');

        $this->getJson("/api/v1/blood-bank/issues/available-bags?component=packed_red_cells&patient_id={$patient->id}")
            ->assertOk()
            ->assertJsonPath('data.0.id', $bag->id)
            ->assertJsonPath('data.0.compatible', true);

        $issueResponse = $this->postJson('/api/v1/blood-bank/issues', [
            'blood_bag_id' => $bag->id,
            'patient_id'   => $patient->id,
            'physician_id' => $physician->id,
            'indication'   => 'Surgical blood loss',
        ]);

        $issueResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.patient.mrn', 'MRN-API-001')
            ->assertJsonPath('data.physician.name', 'Dr API');

        $issueId = $issueResponse->json('data.id');
        $this->assertDatabaseHas('bb_blood_bags', ['id' => $bag->id, 'status' => 'issued']);
        $this->assertDatabaseHas('bb_blood_issues', [
            'id'          => $issueId,
            'hospital_id' => $hospital->id,
            'patient_id'  => $patient->id,
        ]);

        $this->getJson("/api/v1/blood-bank/issues/{$issueId}")
            ->assertOk()
            ->assertJsonPath('data.id', $issueId);

        $this->getJson('/api/v1/blood-bank/issues?search=MRN-API-001')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1);

        $this->patchJson("/api/v1/blood-bank/issues/{$issueId}/outcome", [
            'transfusion_started_at'   => '2026-11-01 10:00:00',
            'transfusion_completed_at' => '2026-11-01 11:00:00',
            'reaction_noted'           => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.reaction_noted', false);

        $this->assertDatabaseHas('bb_blood_issues', [
            'id'                       => $issueId,
            'transfusion_started_at'   => '2026-11-01 10:00:00',
            'transfusion_completed_at' => '2026-11-01 11:00:00',
            'reaction_noted'           => false,
        ]);

        $this->getJson('/api/v1/blood-bank/dashboard')
            ->assertOk()
            ->assertJsonPath('data.summary.available_units', 0);
    }

    public function test_blood_bag_api_releases_quarantine_and_records_discard(): void
    {
        $this->actingAs($this->administrator(), 'api');
        $storageUnit = StorageUnit::create(['name' => 'Lifecycle fridge', 'is_active' => true]);
        $bag = $this->availableBag($storageUnit, 'BAG-LIFECYCLE-001');
        $bag->update(['status' => 'quarantined']);

        $this->patchJson("/api/v1/blood-bank/bags/{$bag->id}/release")
            ->assertOk()
            ->assertJsonPath('data.status', 'available');

        $this->patchJson("/api/v1/blood-bank/bags/{$bag->id}/discard", ['reason' => 'Damaged bag'])
            ->assertOk()
            ->assertJsonPath('data.status', 'discarded')
            ->assertJsonPath('data.discard_reason', 'Damaged bag');

        $this->assertDatabaseHas('bb_blood_bags', [
            'id'             => $bag->id,
            'status'         => 'discarded',
            'discard_reason' => 'Damaged bag',
        ]);
    }

    public function test_returns_409_when_releasing_a_unit_that_is_not_quarantined(): void
    {
        $this->actingAs($this->administrator(), 'api');
        $storageUnit = StorageUnit::create(['name' => 'Available fridge', 'is_active' => true]);
        $bag = $this->availableBag($storageUnit, 'BAG-NOT-QUARANTINED');

        $this->patchJson("/api/v1/blood-bank/bags/{$bag->id}/release")
            ->assertConflict()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('bb_blood_bags', ['id' => $bag->id, 'status' => 'available']);
    }

    public function test_authorized_user_can_register_verify_and_broadcast_to_donors(): void
    {
        Notification::fake();

        $user = $this->administrator();
        $hospital = $this->hospitalFor($user, 23.7808, 90.4070);
        $this->actingAs($user, 'api');

        $donorResponse = $this->postJson('/api/v1/blood-bank/donors', [
            'name'        => 'Nearby Donor',
            'email'       => 'donor@example.test',
            'blood_group' => 'O+',
            'latitude'    => 23.7810,
            'longitude'   => 90.4070,
        ]);

        $donorResponse->assertCreated()
            ->assertJsonPath('data.is_verified', false);
        $donorId = $donorResponse->json('data.id');
        $donor = BloodDonor::findOrFail($donorId);

        $this->patchJson("/api/v1/blood-bank/donors/{$donorId}/verify", ['verified' => true])
            ->assertOk()
            ->assertJsonPath('data.is_verified', true);

        $this->getJson('/api/v1/blood-bank/donors?verified=1')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.id', $donorId);

        $response = $this->postJson('/api/v1/blood-bank/emergency', [
            'blood_group'  => 'O+',
            'component'    => 'packed_red_cells',
            'units_needed' => 2,
            'urgency'      => 'urgent',
            'radius_km'    => 15,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.hospital', $hospital->hospital_name)
            ->assertJsonPath('data.donors_notified_count', 1);

        $requestId = $response->json('data.id');
        $this->assertDatabaseHas('bb_emergency_request_donor', [
            'emergency_request_id' => $requestId,
            'blood_donor_id'       => $donor->id,
            'response'             => 'pending',
        ]);
        Notification::assertSentTo($donor, EmergencyBloodRequestNotification::class);

        $this->getJson('/api/v1/blood-bank/emergency?status=open')
            ->assertOk()
            ->assertJsonPath('pagination.total', 1);

        $this->getJson("/api/v1/blood-bank/emergency/{$requestId}")
            ->assertOk()
            ->assertJsonPath('data.donors.0.response', 'pending');

        $this->patchJson("/api/v1/blood-bank/emergency/{$requestId}/fulfill")
            ->assertOk()
            ->assertJsonPath('data.status', 'fulfilled');

        $cancelResponse = $this->postJson('/api/v1/blood-bank/emergency', [
            'blood_group'  => 'O+',
            'component'    => 'packed_red_cells',
            'units_needed' => 1,
            'urgency'      => 'urgent',
            'radius_km'    => 15,
        ]);
        $cancelResponse->assertCreated();

        $this->patchJson('/api/v1/blood-bank/emergency/'.$cancelResponse->json('data.id').'/cancel')
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    private function administrator(): User
    {
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'api']);
        $user = User::factory()->create([
            'first_name' => 'Blood',
            'last_name'  => 'Bank',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function hospitalFor(User $user, float $latitude = 23.7808, float $longitude = 90.4070): Hospital
    {
        return Hospital::create([
            'hospital_name' => 'API General Hospital',
            'mobile_number' => '0123456789',
            'address'       => '1 Hospital Road',
            'latitude'      => $latitude,
            'longitude'     => $longitude,
            'created_by'    => $user->id,
        ]);
    }

    private function availableBag(StorageUnit $storageUnit, string $bagNumber): BloodBag
    {
        return BloodBag::create([
            'bag_number'      => $bagNumber,
            'batch_number'    => 'BATCH-'.$bagNumber,
            'blood_group'     => 'O+',
            'component'       => 'packed_red_cells',
            'volume_ml'       => 300,
            'collected_at'    => now()->subDay(),
            'expires_at'      => now()->addDays(30),
            'storage_unit_id' => $storageUnit->id,
            'status'          => 'available',
        ]);
    }
}
