<?php

namespace Tests\Feature\Api;

use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PharmacyRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacy_user_can_create_a_pharmacy_and_address(): void
    {
        $user = $this->userWithRole('Pharmacy');

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/pharmacies', [
                'name'        => 'Green Cross Pharmacy',
                'phone'       => '01700000000',
                'email'       => 'green@example.test',
                'description' => 'Community pharmacy',
                'user_id'     => 999,
                'status'      => 'approved',
                'address'     => [
                    'address_line_1' => '12 Main Road',
                    'district'       => 'Dhaka',
                    'division'       => 'Dhaka',
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('code', 201)
            ->assertJsonPath('data.name', 'Green Cross Pharmacy')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.address.address_line_1', '12 Main Road');

        $this->assertDatabaseHas('pharmacies', [
            'user_id' => $user->id,
            'name'    => 'Green Cross Pharmacy',
            'status'  => 'pending',
        ]);
        $this->assertDatabaseHas('pharmacy_addresses', [
            'pharmacy_id'    => $response->json('data.id'),
            'address_line_1' => '12 Main Road',
            'district'       => 'Dhaka',
        ]);
    }

    public function test_non_pharmacy_user_cannot_create_a_pharmacy(): void
    {
        $user = $this->userWithRole('Doctor');

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/pharmacies', [
                'name'    => 'Unauthorized Pharmacy',
                'address' => ['address_line_1' => '1 Main Road'],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('pharmacies', [
            'name' => 'Unauthorized Pharmacy',
        ]);
    }

    public function test_pharmacy_user_must_provide_a_valid_address(): void
    {
        $user = $this->userWithRole('Pharmacy');

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/pharmacies', [
                'name'    => 'Missing Address Pharmacy',
                'address' => [
                    'address_line_1' => '',
                    'latitude'       => 120,
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'address.address_line_1',
                'address.latitude',
            ]);

        $this->assertDatabaseMissing('pharmacies', [
            'name' => 'Missing Address Pharmacy',
        ]);
    }

    public function test_pharmacy_user_cannot_register_a_second_pharmacy(): void
    {
        $user = $this->userWithRole('Pharmacy');
        Pharmacy::query()->create([
            'user_id' => $user->id,
            'name'    => 'Existing Pharmacy',
            'slug'    => 'existing-pharmacy',
            'status'  => 'pending',
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/pharmacies', [
                'name'    => 'Second Pharmacy',
                'address' => ['address_line_1' => '1 Main Road'],
            ])
            ->assertConflict()
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('pharmacies', [
            'name' => 'Second Pharmacy',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_a_pharmacy(): void
    {
        $this->postJson('/api/v1/pharmacies', [
            'name'    => 'Unauthenticated Pharmacy',
            'address' => ['address_line_1' => '1 Main Road'],
        ])->assertUnauthorized();
    }

    public function test_pharmacy_user_can_upload_a_license_document_for_their_pharmacy(): void
    {
        Storage::fake('local');
        $user = $this->userWithRole('Pharmacy');
        $pharmacy = Pharmacy::query()->create([
            'user_id' => $user->id,
            'name'    => 'Green Cross Pharmacy',
            'slug'    => 'green-cross-pharmacy',
            'status'  => 'pending',
        ]);

        $response = $this->actingAs($user, 'api')
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/pharmacies/licenses', [
                'license_number' => 'DL-12345',
                'license_type'   => 'drug_license',
                'issued_date'    => '2025-01-01',
                'expiry_date'    => '2027-01-01',
                'document'       => UploadedFile::fake()->create('license.pdf', 250, 'application/pdf'),
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.license_number', 'DL-12345')
            ->assertJsonPath('data.status', 'pending');

        $license = PharmacyLicense::query()->findOrFail($response->json('data.id'));
        $this->assertSame($pharmacy->id, $license->pharmacy_id);
        $this->assertDatabaseHas('pharmacy_licenses', [
            'id'             => $license->id,
            'license_number' => 'DL-12345',
            'status'         => 'pending',
        ]);
        Storage::disk('local')->assertExists($license->document);
    }

    public function test_license_document_upload_requires_a_registered_pharmacy(): void
    {
        Storage::fake('local');
        $user = $this->userWithRole('Pharmacy');

        $this->actingAs($user, 'api')
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/pharmacies/licenses', [
                'license_number' => 'DL-12345',
                'license_type'   => 'drug_license',
                'document'       => UploadedFile::fake()->create('license.pdf', 250, 'application/pdf'),
            ])
            ->assertConflict()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('pharmacy_licenses', 0);
        Storage::disk('local')->assertDirectoryEmpty('pharmacies/licenses');
    }

    public function test_license_document_upload_rejects_an_unsupported_file(): void
    {
        Storage::fake('local');
        $user = $this->userWithRole('Pharmacy');
        Pharmacy::query()->create([
            'user_id' => $user->id,
            'name'    => 'Green Cross Pharmacy',
            'slug'    => 'green-cross-pharmacy',
            'status'  => 'pending',
        ]);

        $this->actingAs($user, 'api')
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/pharmacies/licenses', [
                'license_number' => 'DL-12345',
                'license_type'   => 'drug_license',
                'document'       => UploadedFile::fake()->create('license.exe', 250, 'application/octet-stream'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['document']);

        $this->assertDatabaseCount('pharmacy_licenses', 0);
        Storage::disk('local')->assertDirectoryEmpty('pharmacies/licenses');
    }

    public function test_non_pharmacy_user_cannot_upload_a_license_document(): void
    {
        Storage::fake('local');
        $user = $this->userWithRole('Doctor');

        $this->actingAs($user, 'api')
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/pharmacies/licenses', [
                'license_number' => 'DL-12345',
                'license_type'   => 'drug_license',
                'document'       => UploadedFile::fake()->create('license.pdf', 250, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('pharmacy_licenses', 0);
        Storage::disk('local')->assertDirectoryEmpty('pharmacies/licenses');
    }

    public function test_unauthenticated_user_cannot_upload_a_license_document(): void
    {
        Storage::fake('local');

        $this->withHeader('Accept', 'application/json')
            ->post('/api/v1/pharmacies/licenses', [
                'license_number' => 'DL-12345',
                'license_type'   => 'drug_license',
                'document'       => UploadedFile::fake()->create('license.pdf', 250, 'application/pdf'),
            ])
            ->assertUnauthorized();

        $this->assertDatabaseCount('pharmacy_licenses', 0);
        Storage::disk('local')->assertDirectoryEmpty('pharmacies/licenses');
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
}
