<?php

namespace Tests\Feature;

use App\Jobs\SendFirebasePushNotification;
use App\Models\FirebaseDeviceToken;
use App\Models\User;
use App\Notifications\ApiPostActionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FirebasePushNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_register_a_firebase_device_token(): void
    {
        $user = User::factory()->create();
        $deviceToken = 'firebase-device-token-value';

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/auth/device-token', ['token' => $deviceToken]);

        $response->assertOk()
            ->assertJsonPath('success', true);
        $this->assertDatabaseHas('firebase_device_tokens', [
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $deviceToken),
        ]);
    }

    public function test_device_token_registration_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/device-token', [
            'token' => 'firebase-device-token-value',
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseCount('firebase_device_tokens', 0);
    }

    public function test_device_token_registration_rejects_a_missing_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/auth/device-token');

        $response->assertUnprocessable();
        $this->assertDatabaseCount('firebase_device_tokens', 0);
    }

    public function test_successful_authenticated_api_post_queues_a_contextual_push(): void
    {
        Queue::fake([SendFirebasePushNotification::class]);
        $user = User::factory()->create();
        $deviceToken = FirebaseDeviceToken::create([
            'user_id'    => $user->id,
            'token'      => 'firebase-device-token-value',
            'token_hash' => hash('sha256', 'firebase-device-token-value'),
        ]);

        Route::middleware(['api', 'auth:api'])
            ->post('/api/v1/test-firebase-push', fn () => response()->noContent())
            ->name('api.v1.test-firebase-push');

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/test-firebase-push');

        $response->assertNoContent();
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'type'            => ApiPostActionNotification::class,
        ]);
        Queue::assertPushed(SendFirebasePushNotification::class, function (SendFirebasePushNotification $job) use ($deviceToken): bool {
            return $job->deviceTokenId === $deviceToken->id
                && $job->title === 'Action completed'
                && $job->message === 'The Test Firebase Push action completed successfully.'
                && $job->data === [
                    'type'   => 'api_post_action',
                    'action' => 'Test Firebase Push',
                ];
        });

        $notificationResponse = $this->getJson('/api/v1/notifications');

        $notificationResponse->assertOk()
            ->assertJsonPath('data.0.title', 'Action completed')
            ->assertJsonPath('data.0.message', 'The Test Firebase Push action completed successfully.')
            ->assertJsonPath('data.0.icon', 'checkCircle2')
            ->assertJsonPath('data.0.colorHex', '#4CAF50');
    }

    public function test_unsuccessful_authenticated_api_post_does_not_queue_a_push(): void
    {
        Queue::fake([SendFirebasePushNotification::class]);
        $user = User::factory()->create();
        FirebaseDeviceToken::create([
            'user_id'    => $user->id,
            'token'      => 'firebase-device-token-value',
            'token_hash' => hash('sha256', 'firebase-device-token-value'),
        ]);

        Route::middleware(['api', 'auth:api'])
            ->post('/api/v1/test-failed-firebase-push', fn () => response()->json([], 422))
            ->name('api.v1.test-failed-firebase-push');

        $response = $this->actingAs($user, 'api')->postJson('/api/v1/test-failed-firebase-push');

        $response->assertUnprocessable();
        Queue::assertNothingPushed();
        $this->assertDatabaseCount('notifications', 0);
    }
}
