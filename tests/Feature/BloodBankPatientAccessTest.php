<?php

namespace Tests\Feature;

use App\Jobs\SendFirebasePushNotification;
use App\Models\FirebaseDeviceToken;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\EmergencyRequest;
use App\Modules\BloodBank\Models\StorageUnit;
use App\Modules\BloodBank\Notifications\EmergencyBloodRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BloodBankPatientAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_blood_bank_directory_shows_only_verified_hospitals_and_their_live_stock(): void
    {
        $patient = $this->patient();
        $hospital = $this->hospital('Central Hospital', 'approved');
        $unverifiedHospital = $this->hospital('Unverified Hospital', 'pending');
        $storageUnit = $this->storageUnit($hospital);
        $unverifiedStorageUnit = $this->storageUnit($unverifiedHospital);

        $this->bloodBag($storageUnit, 'DIRECTORY-AVAILABLE', 'available', now()->addDays(10));
        $this->bloodBag($storageUnit, 'DIRECTORY-EXPIRED', 'available', now()->subDay());
        $this->bloodBag($storageUnit, 'DIRECTORY-QUARANTINED', 'quarantined', now()->addDays(10));
        $this->bloodBag($unverifiedStorageUnit, 'DIRECTORY-UNVERIFIED', 'available', now()->addDays(10));

        $this->actingAs($patient)
            ->get(route('patient.blood-bank.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Patient/BloodBank/Index')
                ->has('banks', 1)
                ->where('banks.0.id', $hospital->id)
                ->where('banks.0.name', 'Central Hospital')
                ->where('banks.0.phone', '01722222222')
                ->where('banks.0.availability.O+.packed_red_cells', 1));
    }

    public function test_patient_can_broadcast_an_emergency_and_call_only_donors_who_accept(): void
    {
        Notification::fake();
        Queue::fake();

        $patient = $this->patient();
        $hospital = $this->hospital('Central Hospital', 'approved');
        $this->storageUnit($hospital);
        $donorUser = User::factory()->create([
            'first_name' => 'Nearby',
            'last_name'  => 'Donor',
        ]);
        $donor = BloodDonor::query()->create([
            'user_id'      => $donorUser->id,
            'name'         => 'Nearby Donor',
            'phone'        => '01711111111',
            'email'        => 'donor@example.test',
            'blood_group'  => 'O+',
            'latitude'     => 23.7809,
            'longitude'    => 90.4071,
            'is_verified'  => true,
            'is_available' => true,
        ]);
        $deviceToken = FirebaseDeviceToken::query()->create([
            'user_id'    => $donorUser->id,
            'token'      => 'test-device-token',
            'token_hash' => hash('sha256', 'test-device-token'),
        ]);

        $this->actingAs($patient)
            ->from(route('patient.blood-bank.index'))
            ->post(route('patient.blood-bank.emergency.store'), [
                'hospital_id'  => $hospital->id,
                'blood_group'  => 'O+',
                'component'    => 'packed_red_cells',
                'units_needed' => 2,
                'urgency'      => 'urgent',
                'radius_km'    => 15,
            ])
            ->assertRedirect(route('patient.blood-bank.index'))
            ->assertSessionHas('success');

        $emergencyRequest = EmergencyRequest::query()->where('requested_by', $patient->id)->firstOrFail();
        $this->assertDatabaseHas('bb_emergency_requests', [
            'id'                    => $emergencyRequest->id,
            'hospital_id'           => $hospital->id,
            'requested_by'          => $patient->id,
            'donors_notified_count' => 1,
        ]);
        $this->assertDatabaseHas('bb_emergency_request_donor', [
            'emergency_request_id' => $emergencyRequest->id,
            'blood_donor_id'       => $donor->id,
            'response'             => 'pending',
        ]);
        Notification::assertSentTo($donor, EmergencyBloodRequestNotification::class);
        Queue::assertPushed(
            SendFirebasePushNotification::class,
            fn (SendFirebasePushNotification $job): bool => $job->deviceTokenId === $deviceToken->id
                && $job->data['type'] === 'blood_donation_request'
                && $job->data['request_id'] === (string) $emergencyRequest->id
                && str_contains($job->data['action_url'], 'signature='),
        );

        $this->get(route('patient.blood-bank.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Patient/BloodBank/Index')
                ->has('requests.0.donors', 0));

        $emergencyRequest->donors()->updateExistingPivot($donor->id, [
            'response'     => 'accepted',
            'responded_at' => now(),
        ]);

        $this->get(route('patient.blood-bank.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Patient/BloodBank/Index')
                ->has('requests.0.donors', 1)
                ->where('requests.0.donors.0.phone', '01711111111'));
    }

    public function test_emergency_requests_from_another_patient_are_not_visible_or_cancellable(): void
    {
        $owner = $this->patient();
        $otherPatient = $this->patient();
        $hospital = $this->hospital('Central Hospital', 'approved');
        $emergencyRequest = EmergencyRequest::query()->create([
            'reference'    => 'EMR-OTHER-001',
            'hospital_id'  => $hospital->id,
            'requested_by' => $owner->id,
            'blood_group'  => 'O+',
            'component'    => 'packed_red_cells',
            'units_needed' => 1,
            'urgency'      => 'urgent',
            'radius_km'    => 15,
            'status'       => 'open',
            'broadcast_at' => now(),
            'expires_at'   => now()->addHours(24),
        ]);

        $this->actingAs($otherPatient)
            ->get(route('patient.blood-bank.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Patient/BloodBank/Index')
                ->has('requests', 0));

        $this->patch(route('patient.blood-bank.emergency.cancel', $emergencyRequest))
            ->assertNotFound();

        $this->assertDatabaseHas('bb_emergency_requests', [
            'id'     => $emergencyRequest->id,
            'status' => 'open',
        ]);
    }

    public function test_patient_can_register_as_a_donor_and_pause_alerts(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)
            ->from(route('patient.blood-bank.index'))
            ->post(route('patient.blood-bank.donor-profile.store'), [
                'name'            => 'Patient Donor',
                'phone'           => '01733333333',
                'blood_group'     => 'A+',
                'latitude'        => 23.7808,
                'longitude'       => 90.4070,
                'city'            => 'Dhaka',
                'last_donated_at' => null,
                'consent'         => true,
            ])
            ->assertRedirect(route('patient.blood-bank.index'))
            ->assertSessionHas('success');

        $donor = BloodDonor::query()->where('user_id', $patient->id)->firstOrFail();

        $this->assertFalse($donor->is_verified);
        $this->assertTrue($donor->is_available);
        $this->assertSame($patient->email, $donor->email);

        $this->patch(route('patient.blood-bank.donor-profile.availability'), [
            'is_available' => false,
        ])
            ->assertRedirect(route('patient.blood-bank.index'))
            ->assertSessionHas('success', 'Donor alerts are paused.');

        $this->assertDatabaseHas('bb_donors', [
            'id'           => $donor->id,
            'is_available' => false,
        ]);
    }

    public function test_patient_can_update_donor_details_from_their_existing_profile(): void
    {
        $patient = $this->patient();
        $donor = BloodDonor::query()->create([
            'user_id'      => $patient->id,
            'name'         => 'Patient User',
            'phone'        => '01733333333',
            'email'        => $patient->email,
            'blood_group'  => 'A+',
            'latitude'     => 23.7808,
            'longitude'    => 90.4070,
            'city'         => 'Dhaka',
            'is_verified'  => true,
            'verified_at'  => now(),
            'is_available' => true,
            'consented_at' => now(),
        ]);

        $this->actingAs($patient)
            ->patch(route('patient.blood-bank.donor-profile.update'), [
                'name'            => 'Patient User',
                'phone'           => '01733333333',
                'blood_group'     => 'B+',
                'latitude'        => 23.7808,
                'longitude'       => 90.4070,
                'city'            => 'Dhaka',
                'last_donated_at' => '2026-10-01',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Donor profile updated. Your changed blood group needs verification before you can receive alerts.');

        $this->assertDatabaseHas('bb_donors', [
            'id'           => $donor->id,
            'user_id'      => $patient->id,
            'blood_group'  => 'B+',
            'is_verified'  => false,
            'verified_at'  => null,
            'is_available' => true,
        ]);
        $this->assertSame('2026-10-01', $donor->fresh()->last_donated_at->toDateString());
    }

    public function test_existing_patient_profile_receives_its_donor_details(): void
    {
        $patient = $this->patient();
        Patient::query()->create([
            'patient_number' => 'PATIENT-PROFILE-'.$patient->id,
            'name'           => 'Patient User',
            'age'            => '30',
            'phone'          => '01733333333',
            'user_id'        => $patient->id,
            'blood_group'    => 'A+',
        ]);
        BloodDonor::query()->create([
            'user_id'         => $patient->id,
            'name'            => 'Patient User',
            'phone'           => '01733333333',
            'email'           => $patient->email,
            'blood_group'     => 'A+',
            'latitude'        => 23.7808,
            'longitude'       => 90.4070,
            'last_donated_at' => '2026-10-01',
            'is_available'    => true,
        ]);

        $this->actingAs($patient)
            ->get(route('patients.profile.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Profile/Patient/Edit')
                ->where('donor.blood_group', 'A+')
                ->where('donor.last_donated_at', '2026-10-01')
                ->where('donor.is_available', true));
    }

    public function test_patient_must_consent_before_joining_the_donor_network(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)
            ->from(route('patient.blood-bank.index'))
            ->post(route('patient.blood-bank.donor-profile.store'), [
                'name'        => 'Patient Donor',
                'phone'       => '01733333333',
                'blood_group' => 'A+',
                'latitude'    => 23.7808,
                'longitude'   => 90.4070,
            ])
            ->assertRedirect(route('patient.blood-bank.index'))
            ->assertSessionHasErrors('consent');

        $this->assertDatabaseMissing('bb_donors', ['user_id' => $patient->id]);
    }

    public function test_blood_bank_pages_require_an_authenticated_patient(): void
    {
        $this->get(route('patient.blood-bank.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create([
            'first_name' => 'Regular',
            'last_name'  => 'User',
        ]))
            ->get(route('patient.blood-bank.index'))
            ->assertRedirect();
    }

    private function patient(): User
    {
        Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'web',
        ]);
        $role = Role::firstOrCreate([
            'name'       => 'Patient',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create([
            'first_name' => 'Patient',
            'last_name'  => 'User',
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function hospital(string $name, string $verificationStatus): Hospital
    {
        return Hospital::query()->create([
            'hospital_name'       => $name,
            'mobile_number'       => '01700000000',
            'emergency_contact'   => '01722222222',
            'address'             => '1 Hospital Road',
            'latitude'            => 23.7808,
            'longitude'           => 90.4070,
            'verification_status' => $verificationStatus,
        ]);
    }

    private function storageUnit(Hospital $hospital): StorageUnit
    {
        return StorageUnit::query()->create([
            'name'        => $hospital->hospital_name.' fridge',
            'type'        => 'refrigerator',
            'hospital_id' => $hospital->id,
        ]);
    }

    private function bloodBag(StorageUnit $storageUnit, string $bagNumber, string $status, mixed $expiresAt): BloodBag
    {
        return BloodBag::query()->create([
            'bag_number'      => $bagNumber,
            'batch_number'    => 'BATCH-'.$bagNumber,
            'blood_group'     => 'O+',
            'component'       => 'packed_red_cells',
            'volume_ml'       => 300,
            'collected_at'    => now()->subDay(),
            'expires_at'      => $expiresAt,
            'storage_unit_id' => $storageUnit->id,
            'status'          => $status,
        ]);
    }
}
