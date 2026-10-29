<?php

namespace Tests\Feature\Admin;

use App\Models\Pharmacist;
use App\Models\Pharmacy;
use App\Models\PharmacyAddress;
use App\Models\PharmacyInventoryItem;
use App\Models\PharmacyLicense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacyAnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_pharmacy_registration_and_credential_analytics(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(12, 0));
        $admin = $this->userWithRole('Admin');
        $owner = $this->userWithRole('Pharmacy');
        $approved = $this->createPharmacy($owner, 'approved', '2026-10-12 10:00:00');
        $pending = $this->createPharmacy(
            $this->userWithRole('Pharmacy'),
            'pending',
            '2026-09-10 10:00:00',
        );
        PharmacyAddress::query()->create([
            'pharmacy_id'    => $approved->id,
            'address_line_1' => '12 Main Road',
            'district'       => 'Dhaka',
            'division'       => 'Dhaka',
        ]);
        PharmacyLicense::query()->create([
            'pharmacy_id'    => $approved->id,
            'license_number' => 'LIC-1001',
            'license_type'   => 'drug_license',
            'status'         => 'verified',
        ]);
        PharmacyLicense::query()->create([
            'pharmacy_id'    => $pending->id,
            'license_number' => 'LIC-1002',
            'license_type'   => 'drug_license',
            'status'         => 'pending',
        ]);
        Pharmacist::query()->create([
            'pharmacy_id' => $approved->id,
            'name'        => 'Verified Pharmacist',
            'status'      => 'verified',
        ]);
        Pharmacist::query()->create([
            'pharmacy_id' => $pending->id,
            'name'        => 'Pending Pharmacist',
            'status'      => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pharmacies.analytics'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Pharmacies/Analytics')
                ->where('summary.pharmacies.total', 2)
                ->where('summary.pharmacies.approved', 1)
                ->where('summary.pharmacies.pending', 1)
                ->where('summary.licenses.verified', 1)
                ->where('summary.licenses.pending', 1)
                ->where('summary.pharmacists.verified', 1)
                ->where('summary.pharmacists.pending', 1)
                ->where('summary.pendingReviews', 3)
                ->where('registrationTrend.4.registrations', 1)
                ->where('registrationTrend.5.registrations', 1)
                ->where('locations.0.district', 'Dhaka')
                ->where('recentPharmacies.0.id', $approved->id));
    }

    public function test_pharmacy_user_is_redirected_from_pharmacy_analytics(): void
    {
        $user = $this->userWithRole('Pharmacy');

        $this->actingAs($user)
            ->get(route('admin.pharmacies.analytics'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_pharmacy_dashboard_renders_owner_scoped_operations_analytics(): void
    {
        $owner = $this->userWithRole('Pharmacy');
        $otherOwner = $this->userWithRole('Pharmacy');
        $pharmacy = $this->createPharmacy($owner, 'approved', '2026-10-12 10:00:00');
        $otherPharmacy = $this->createPharmacy($otherOwner, 'approved', '2026-10-12 10:00:00');
        PharmacyInventoryItem::query()->create([
            'pharmacy_id'    => $pharmacy->id,
            'medicine_name'  => 'Owner low-stock medicine',
            'stock_quantity' => 2,
            'reorder_level'  => 5,
            'expiry_date'    => today()->addDays(20),
        ]);
        PharmacyInventoryItem::query()->create([
            'pharmacy_id'    => $otherPharmacy->id,
            'medicine_name'  => 'Other pharmacy medicine',
            'stock_quantity' => 0,
            'reorder_level'  => 5,
            'expiry_date'    => today()->addDays(10),
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Pharmacies/Analytics')
                ->where('audience', 'pharmacy')
                ->where('summary.pharmacies.total', 1)
                ->where('operations.lowStockDrugs', 1)
                ->where('operations.expiringSoon', 1)
                ->where('operations.lowStockItems.0.medicine_name', 'Owner low-stock medicine')
                ->where('operations.expiringSoonItems.0.medicine_name', 'Owner low-stock medicine'));
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

    private function createPharmacy(User $owner, string $status, string $createdAt): Pharmacy
    {
        return Pharmacy::query()->create([
            'user_id'    => $owner->id,
            'name'       => 'Central Care',
            'slug'       => 'central-care-'.$owner->id,
            'status'     => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
