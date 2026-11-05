<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SslCommerzService
{
    public function createSession(array $data): array
    {
        $this->assertConfigured();
        $response = Http::asForm()->timeout(config('sslcommerz.timeout'))->post(config('sslcommerz.session_url'), array_merge([
            'store_id' => config('sslcommerz.store_id'), 'store_passwd' => config('sslcommerz.store_password'),
        ], $data));
        $response->throw();
        $json = $response->json();
        if (! is_array($json) || ($json['status'] ?? null) !== 'SUCCESS' || empty($json['GatewayPageURL'])) {
            throw new RuntimeException('SSLCommerz could not create a payment session.');
        }

        return $json;
    }

    public function validate(string $valId): array
    {
        $this->assertConfigured();
        $response = Http::timeout(config('sslcommerz.timeout'))->get(config('sslcommerz.validation_url'), [
            'val_id'       => $valId, 'store_id' => config('sslcommerz.store_id'),
            'store_passwd' => config('sslcommerz.store_password'), 'format' => 'json',
        ]);
        $response->throw();
        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Invalid SSLCommerz validation response.');
        }

        return $json;
    }

    private function assertConfigured(): void
    {
        if (! config('sslcommerz.store_id') || ! config('sslcommerz.store_password')) {
            throw new RuntimeException('SSLCommerz credentials are not configured.');
        }
    }
}
