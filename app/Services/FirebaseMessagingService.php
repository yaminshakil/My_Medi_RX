<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JsonException;
use RuntimeException;
use UnexpectedValueException;

class FirebaseMessagingService
{
    private const TOKEN_URI = 'https://oauth2.googleapis.com/token';

    /**
     * @param  array<string, string>  $data
     */
    public function send(string $deviceToken, string $title, string $body, array $data): void
    {
        $credentials = $this->credentials();
        $projectId = config('services.firebase.project_id') ?: ($credentials['project_id'] ?? null);

        if (! is_string($projectId) || $projectId === '') {
            throw new UnexpectedValueException('Firebase project ID is missing.');
        }

        Http::withToken($this->accessToken($credentials))
            ->acceptJson()
            ->timeout(10)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token'        => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $data,
                ],
            ])
            ->throw();
    }

    /**
     * @return array<string, mixed>
     */
    private function credentials(): array
    {
        $path = config('services.firebase.credentials');

        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            throw new RuntimeException('Firebase credentials file is not configured or readable.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Firebase credentials file could not be read.');
        }

        try {
            $credentials = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Firebase credentials file contains invalid JSON.', previous: $exception);
        }

        if (
            ! is_array($credentials)
            || ! is_string($credentials['client_email'] ?? null)
            || ! is_string($credentials['private_key'] ?? null)
        ) {
            throw new UnexpectedValueException('Firebase credentials must include a client email and private key.');
        }

        return $credentials;
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function accessToken(array $credentials): string
    {
        $cacheKey = 'firebase.messaging.access_token.'.hash('sha256', $credentials['client_email']);

        return Cache::remember($cacheKey, now()->addSeconds(3300), function () use ($credentials): string {
            $issuedAt = now()->timestamp;
            $assertion = $this->signedAssertion($credentials, $issuedAt);
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(10)
                ->post(self::TOKEN_URI, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $assertion,
                ])
                ->throw();

            $accessToken = $response->json('access_token');

            if (! is_string($accessToken) || $accessToken === '') {
                throw new UnexpectedValueException('Google OAuth response did not contain an access token.');
            }

            return $accessToken;
        });
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function signedAssertion(array $credentials, int $issuedAt): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $claims = $this->base64UrlEncode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => self::TOKEN_URI,
            'iat'   => $issuedAt,
            'exp'   => $issuedAt + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsignedAssertion = "{$header}.{$claims}";
        $privateKey = openssl_pkey_get_private($credentials['private_key']);

        if ($privateKey === false || ! openssl_sign($unsignedAssertion, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Firebase credentials could not be used to sign an OAuth assertion.');
        }

        return $unsignedAssertion.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
