<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HospitalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_the_hospital_role_cannot_create_or_update_hospitals(): void
    {
        $user = $this->userWithRole('Doctor');

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/hospitals', [])
            ->assertForbidden();

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/hospitals/1', [])
            ->assertForbidden();
    }

    public function test_hospital_role_users_can_reach_hospital_create_and_update_validation(): void
    {
        $user = $this->userWithRole('Hospital');

        $this->actingAs($user, 'api')
            ->postJson('/api/v1/hospitals', [])
            ->assertUnprocessable();

        $this->actingAs($user, 'api')
            ->putJson('/api/v1/hospitals/1', [])
            ->assertUnprocessable();
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
