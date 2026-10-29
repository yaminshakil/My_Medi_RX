<?php

namespace Tests\Feature;

use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_pharmacy_with_its_address(): void
    {
        $admin = $this->userWithRole('Admin');
        $owner = $this->userWithRole('Pharmacy');

        $response = $this->actingAs($admin)->post(route('admin.pharmacies.store'), [
            'user_id' => $owner->id,
            'name'    => 'Central Care Pharmacy',
            'phone'   => '01700000000',
            'email'   => 'central@example.test',
            'address' => [
                'address_line_1' => '12 Main Road',
                'district'       => 'Dhaka',
                'division'       => 'Dhaka',
            ],
        ]);

        $response->assertRedirect(route('admin.pharmacies.show', 1));
        $response->assertSessionHas('success', 'Pharmacy created and queued for review.');
        $this->assertDatabaseHas('pharmacies', [
            'user_id' => $owner->id,
            'name'    => 'Central Care Pharmacy',
            'slug'    => 'central-care-pharmacy',
            'status'  => 'pending',
        ]);
        $this->assertDatabaseHas('pharmacy_addresses', [
            'pharmacy_id'    => 1,
            'address_line_1' => '12 Main Road',
            'district'       => 'Dhaka',
        ]);
    }

    public function test_admin_cannot_approve_a_pharmacy_without_a_verified_license(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'));

        $response = $this->actingAs($admin)->patch(route('admin.pharmacies.status', $pharmacy), [
            'status' => 'approved',
        ]);

        $response->assertSessionHasErrors([
            'status' => 'Verify at least one current pharmacy license before approving this pharmacy.',
        ]);
        $this->assertDatabaseHas('pharmacies', [
            'id'     => $pharmacy->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_a_pharmacy_after_verifying_a_current_license(): void
    {
        $admin = $this->userWithRole('Admin');
        $pharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'));
        PharmacyLicense::query()->create([
            'pharmacy_id'    => $pharmacy->id,
            'license_number' => 'LIC-1001',
            'license_type'   => 'drug_license',
            'expiry_date'    => today()->addYear(),
            'document'       => 'pharmacies/licenses/license.pdf',
            'status'         => 'verified',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.pharmacies.status', $pharmacy), [
            'status' => 'approved',
        ]);

        $response->assertSessionHas('success', 'Pharmacy status updated.');
        $this->assertDatabaseHas('pharmacies', [
            'id'          => $pharmacy->id,
            'status'      => 'approved',
            'verified_by' => $admin->id,
        ]);
    }

    public function test_non_admin_users_are_redirected_from_the_pharmacy_panel(): void
    {
        $user = $this->userWithRole('Pharmacy');

        $this->actingAs($user)
            ->get(route('admin.pharmacies.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_license_routes_are_scoped_to_their_pharmacy(): void
    {
        $admin = $this->userWithRole('Admin');
        $firstPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'));
        $secondPharmacy = $this->createPharmacy($this->userWithRole('Pharmacy'), 'other-pharmacy');
        $license = PharmacyLicense::query()->create([
            'pharmacy_id'    => $secondPharmacy->id,
            'license_number' => 'LIC-2002',
            'license_type'   => 'drug_license',
            'document'       => 'pharmacies/licenses/other.pdf',
            'status'         => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.pharmacies.licenses.review', [$firstPharmacy, $license]), [
                'status' => 'verified',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('pharmacy_licenses', [
            'id'     => $license->id,
            'status' => 'pending',
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate([
            'name'       => $roleName,
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createPharmacy(User $owner, string $slug = 'central-care'): Pharmacy
    {
        return Pharmacy::query()->create([
            'user_id' => $owner->id,
            'name'    => 'Central Care',
            'slug'    => $slug,
            'status'  => 'pending',
        ]);
    }
}
