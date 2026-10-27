<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use App\Notifications\PrescriptionIssuedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_gets_their_notifications_in_notification_center_shape(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notificationId = (string) Str::uuid();
        $this->freezeTime();

        $user->notifications()->create([
            'id'   => $notificationId,
            'type' => 'appointment',
            'data' => [
                'type'    => 'appointment_confirmed',
                'title'   => 'Appointment Confirmed',
                'message' => 'Your appointment has been confirmed.',
                'url'     => '/scheduling/appointments/apt-89412',
            ],
            'created_at' => now()->subMinutes(2),
        ]);
        $otherUser->notifications()->create([
            'id'   => (string) Str::uuid(),
            'type' => 'appointment',
            'data' => [
                'title'   => 'Private notification',
                'message' => 'This belongs to another user.',
            ],
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/notifications');

        $response->assertOk()->assertExactJson([
            'success'    => true,
            'statusCode' => 200,
            'message'    => 'Notifications loaded',
            'data'       => [
                [
                    'id'          => $notificationId,
                    'title'       => 'Appointment Confirmed',
                    'message'     => 'Your appointment has been confirmed.',
                    'time'        => '2 minutes ago',
                    'icon'        => 'calendarCheck',
                    'colorHex'    => '#4CAF50',
                    'isRead'      => false,
                    'actionRoute' => '/scheduling/appointments/apt-89412',
                ],
            ],
        ]);
    }

    public function test_notifications_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/notifications');

        $response->assertUnauthorized();
    }

    public function test_notification_center_displays_a_prescription_issued_notification(): void
    {
        $user = User::factory()->create();
        $user->notifications()->create([
            'id'   => (string) Str::uuid(),
            'type' => PrescriptionIssuedNotification::class,
            'data' => [
                'type'                => 'prescription_issued',
                'title'               => 'New Prescription',
                'message'             => 'Dr. Sarah Khan issued your prescription RX-123.',
                'prescription_id'     => '123',
                'prescription_number' => 'RX-123',
                'actionRoute'         => '/api/v1/patient/prescriptions/123',
            ],
        ]);

        $response = $this->actingAs($user, 'api')->getJson('/api/v1/notifications');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'New Prescription')
            ->assertJsonPath('data.0.message', 'Dr. Sarah Khan issued your prescription RX-123.')
            ->assertJsonPath('data.0.icon', 'checkCircle2')
            ->assertJsonPath('data.0.colorHex', '#FF9800')
            ->assertJsonPath('data.0.actionRoute', '/api/v1/patient/prescriptions/123');
    }

    public function test_doctor_gets_their_notifications_in_shared_inertia_props(): void
    {
        $doctor = User::factory()->create();
        $doctor->notifications()->create([
            'id'   => (string) Str::uuid(),
            'type' => 'appointment',
            'data' => [
                'type'        => 'appointment_booked',
                'title'       => 'New Appointment',
                'message'     => 'Alex Smith booked an appointment.',
                'actionRoute' => '/admin/appointments',
            ],
        ]);

        $this->actingAs($doctor);
        $request = Request::create('/admin/appointments', 'GET');
        $request->setUserResolver(fn () => $doctor);

        $sharedProps = app(HandleInertiaRequests::class)->share($request);
        $notifications = ($sharedProps['notifications'])();

        $this->assertSame(1, $notifications['unread_count']);
        $this->assertSame('New Appointment', $notifications['items']->first()['title']);
        $this->assertSame('/admin/appointments', $notifications['items']->first()['url']);
    }

    public function test_authenticated_user_can_mark_their_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $user->notifications()->create([
            'id'   => (string) Str::uuid(),
            'type' => 'system',
            'data' => ['title' => 'System Update', 'message' => 'An update is available.'],
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()->assertExactJson([
            'success'    => true,
            'statusCode' => 200,
            'message'    => 'Notification marked as read',
        ]);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = $otherUser->notifications()->create([
            'id'   => (string) Str::uuid(),
            'type' => 'system',
            'data' => ['title' => 'Private', 'message' => 'Private notification.'],
        ]);

        $response = $this->actingAs($user, 'api')
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }
}
