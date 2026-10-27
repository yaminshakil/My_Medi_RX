<?php

namespace Tests\Unit\Services;

use App\Services\FirebaseMessagingService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FirebaseMessagingServiceTest extends TestCase
{
    public function test_it_authenticates_and_sends_a_firebase_message(): void
    {
        $privateKeyContents = file_get_contents(base_path('tests/Fixtures/firebase-test-private-key.pem'));
        $this->assertNotFalse($privateKeyContents);
        $credentialsPath = tempnam(sys_get_temp_dir(), 'firebase-credentials-');
        $this->assertNotFalse($credentialsPath);

        try {
            $this->assertNotFalse(file_put_contents($credentialsPath, json_encode([
                'project_id'   => 'test-project',
                'client_email' => 'firebase@example.test',
                'private_key'  => $privateKeyContents,
            ], JSON_THROW_ON_ERROR)));

            config([
                'services.firebase.credentials' => $credentialsPath,
                'services.firebase.project_id'  => 'test-project',
            ]);

            Http::preventStrayRequests();
            Http::fake([
                'https://oauth2.googleapis.com/token' => Http::response([
                    'access_token' => 'firebase-access-token',
                    'expires_in'   => 3600,
                ]),
                'https://fcm.googleapis.com/v1/projects/test-project/messages:send' => Http::response([
                    'name' => 'projects/test-project/messages/test-message',
                ]),
            ]);

            app(FirebaseMessagingService::class)->send(
                'registered-device-token',
                'Action completed',
                'The Appointment Store action completed successfully.',
                [
                    'type'   => 'api_post_action',
                    'action' => 'Appointment Store',
                ],
            );
        } finally {
            unlink($credentialsPath);
        }

        Http::assertSent(function (Request $request): bool {
            if ($request->url() !== 'https://oauth2.googleapis.com/token') {
                return false;
            }

            parse_str($request->body(), $form);
            $assertionParts = explode('.', $form['assertion'] ?? '');

            return ($form['grant_type'] ?? null) === 'urn:ietf:params:oauth:grant-type:jwt-bearer'
                && count($assertionParts) === 3;
        });

        Http::assertSent(fn (Request $request): bool => $request->url()
            === 'https://fcm.googleapis.com/v1/projects/test-project/messages:send'
            && $request->hasHeader('Authorization', 'Bearer firebase-access-token')
            && json_decode($request->body(), true) === [
                'message' => [
                    'token'        => 'registered-device-token',
                    'notification' => [
                        'title' => 'Action completed',
                        'body'  => 'The Appointment Store action completed successfully.',
                    ],
                    'data' => [
                        'type'   => 'api_post_action',
                        'action' => 'Appointment Store',
                    ],
                ],
            ]);
    }
}
