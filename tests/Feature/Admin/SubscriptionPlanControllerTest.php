<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_plan_management_page(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.subscription-plans.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/subscriptions/Plans/Index'));
    }

    public function test_admin_can_load_plan_data_as_json(): void
    {
        $admin = $this->userWithRole('Admin');
        $plan = $this->createPlan(['slug' => 'patient-premium']);

        $this->actingAs($admin)
            ->getJson(route('admin.subscription-plans.index'))
            ->assertOk()
            ->assertJsonPath('data.0.slug', $plan->slug);
    }

    public function test_unauthenticated_user_is_redirected_from_plan_management(): void
    {
        $this->get(route('admin.subscription-plans.index'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden_from_managing_plans(): void
    {
        $patient = $this->userWithRole('Patient');

        $this->actingAs($patient)
            ->get(route('admin.subscription-plans.index'))
            ->assertForbidden();
    }

    public function test_plan_management_menu_is_seeded_for_admins(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->seed(SubscriptionPlanMenuSeeder::class);

        $menu = Menu::query()
            ->where('slug', '/admin/subscription-plans')
            ->firstOrFail();

        $this->assertDatabaseHas('menu_role', [
            'menu_id' => $menu->id,
            'role_id' => $admin->roles()->firstOrFail()->id,
        ]);
    }

    public function test_admin_can_create_free_half_yearly_plan_with_generated_slug(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.subscription-plans.store'), $this->validPlanAttributes([
                'name'          => 'Patient Free',
                'price'         => 0,
                'billing_cycle' => 'half_yearly',
                'duration_days' => 180,
            ]))
            ->assertCreated()
            ->assertJsonPath('message', 'Subscription plan created.');

        $this->assertDatabaseHas('subscription_plans', [
            'name'          => 'Patient Free',
            'price'         => 0,
            'billing_cycle' => 'half_yearly',
            'duration_days' => 180,
            'is_active'     => true,
        ]);
    }

    public function test_plan_creation_rejects_duplicate_slugs(): void
    {
        $admin = $this->userWithRole('Admin');
        $this->createPlan(['slug' => 'patient-premium']);

        $this->actingAs($admin)
            ->postJson(route('admin.subscription-plans.store'), $this->validPlanAttributes([
                'slug' => 'patient-premium',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');

        $this->assertDatabaseCount('subscription_plans', 1);
    }

    public function test_admin_can_update_a_plan_and_keep_its_existing_slug(): void
    {
        $admin = $this->userWithRole('Admin');
        $plan = $this->createPlan(['slug' => 'patient-premium']);

        $this->actingAs($admin)
            ->putJson(route('admin.subscription-plans.update', $plan), $this->validPlanAttributes([
                'name'      => 'Patient Professional',
                'slug'      => $plan->slug,
                'price'     => 499,
                'is_active' => false,
            ]))
            ->assertOk()
            ->assertJsonPath('message', 'Subscription plan updated.');

        $this->assertDatabaseHas('subscription_plans', [
            'id'        => $plan->id,
            'name'      => 'Patient Professional',
            'slug'      => 'patient-premium',
            'price'     => 499,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_a_plan_status_with_patch(): void
    {
        $admin = $this->userWithRole('Admin');
        $plan = $this->createPlan(['slug' => 'patient-premium']);

        $this->actingAs($admin)
            ->patchJson(route('admin.subscription-plans.update', $plan), $this->validPlanAttributes([
                'slug'      => $plan->slug,
                'is_active' => false,
            ]))
            ->assertOk()
            ->assertJsonPath('message', 'Subscription plan updated.');

        $this->assertDatabaseHas('subscription_plans', [
            'id'        => $plan->id,
            'is_active' => false,
        ]);
    }

    public function test_plan_creation_rejects_negative_prices(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->postJson(route('admin.subscription-plans.store'), $this->validPlanAttributes([
                'price' => -1,
            ]))
            ->assertUnprocessable()
            ->assertJsonPath('errors.price.0', 'The price field must be at least 0.');

        $this->assertDatabaseCount('subscription_plans', 0);
    }

    public function test_admin_can_delete_a_plan_without_subscription_history(): void
    {
        $admin = $this->userWithRole('Admin');
        $plan = $this->createPlan(['slug' => 'unused-plan']);

        $this->actingAs($admin)
            ->deleteJson(route('admin.subscription-plans.destroy', $plan))
            ->assertOk()
            ->assertJsonPath('message', 'Subscription plan deleted.');

        $this->assertDatabaseMissing('subscription_plans', ['id' => $plan->id]);
    }

    public function test_admin_deactivates_a_plan_with_subscription_history_instead_of_deleting_it(): void
    {
        $admin = $this->userWithRole('Admin');
        $user = User::factory()->create([
            'first_name' => 'Subscriber',
            'last_name'  => 'User',
        ]);
        $plan = $this->createPlan(['slug' => 'used-plan']);
        Subscription::query()->create([
            'user_id'              => $user->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'active',
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('admin.subscription-plans.destroy', $plan))
            ->assertOk()
            ->assertJsonPath('message', 'Plan deactivated because it has subscription history.');

        $this->assertDatabaseHas('subscription_plans', [
            'id'        => $plan->id,
            'is_active' => false,
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name'  => 'User',
        ]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function validPlanAttributes(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'Patient Premium',
            'description'   => 'Premium patient features.',
            'price'         => 299,
            'billing_cycle' => 'monthly',
            'duration_days' => 30,
            'user_type'     => 'Patient',
            'features'      => ['ai_assistant' => ['enabled' => true, 'limit' => 10]],
            'is_active'     => true,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPlan(array $overrides = []): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create(array_merge([
            'name'          => 'Patient Premium',
            'slug'          => 'patient-premium',
            'description'   => 'Premium patient features.',
            'price'         => 299,
            'billing_cycle' => 'monthly',
            'duration_days' => 30,
            'user_type'     => 'Patient',
            'features'      => ['ai_assistant' => ['enabled' => true, 'limit' => 10]],
            'is_active'     => true,
        ], $overrides));
    }
}
