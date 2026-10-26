<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PostActionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PostActionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_post_notifies_admins(): void
    {
        $admin = $this->createAdmin();
        $actor = User::factory()->create();

        Route::post('/test-post-action', fn () => response()->noContent())
            ->name('test.post-action');

        $response = $this->actingAs($actor)->post('/test-post-action');

        $response->assertNoContent();
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id'   => $admin->id,
            'type'            => PostActionNotification::class,
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id'   => $actor->id,
            'type'            => PostActionNotification::class,
        ]);

        $notification = $admin->notifications()->first();
        $this->assertSame('post_action', $notification->data['type']);
        $this->assertSame('The Test Post Action action completed successfully.', $notification->data['message']);
    }

    public function test_failed_post_does_not_notify_admins(): void
    {
        $this->createAdmin();

        Route::post('/test-failed-post-action', fn () => response()->json([], 422));

        $response = $this->postJson('/test-failed-post-action');

        $response->assertUnprocessable();
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_validation_redirect_does_not_notify_admins(): void
    {
        $this->createAdmin();

        Route::middleware('web')->post(
            '/test-invalid-post-action',
            fn () => back()->withErrors(['value' => 'The value is invalid.'])
        );

        $response = $this->post('/test-invalid-post-action');

        $response->assertRedirect();
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_get_request_does_not_notify_admins(): void
    {
        $this->createAdmin();

        Route::get('/test-get-action', fn () => response()->noContent());

        $response = $this->get('/test-get-action');

        $response->assertNoContent();
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_boost_browser_log_post_does_not_notify_admins(): void
    {
        $this->createAdmin();

        Route::post('/_boost/test-browser-logs', fn () => response()->noContent());

        $response = $this->post('/_boost/test-browser-logs');

        $response->assertNoContent();
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_reading_a_notification_does_not_create_another_notification(): void
    {
        $admin = $this->createAdmin();
        $notification = $admin->notifications()->create([
            'id'   => (string) Str::uuid(),
            'type' => PostActionNotification::class,
            'data' => [
                'type'    => 'post_action',
                'title'   => 'POST action completed',
                'message' => 'The test action completed successfully.',
                'url'     => route('dashboard', absolute: false),
            ],
        ]);

        $response = $this->actingAs($admin)->post(route('notifications.read', $notification->id));

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertDatabaseCount('notifications', 1);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Admin', 'web'));

        return $admin;
    }
}
