<?php

namespace Tests\Feature\Api\V1;

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_clients_can_read_public_site_settings_only(): void
    {
        $this->seed(SettingSeeder::class);
        Setting::setValue('site_title', 'Custom MediRx');
        Setting::setValue('smtp_password', 'private-secret');

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Site settings retrieved successfully.')
            ->assertJsonPath('data.site_title', 'Custom MediRx')
            ->assertJsonPath('data.currency', 'BDT')
            ->assertJsonPath('data.currency_symbol', 'TK')
            ->assertJsonPath('data.logo_path', 'default/logo.png')
            ->assertJsonPath('data.navbar_logo_path', null)
            ->assertJsonPath('data.site_email', 'info@nsbd.net')
            ->assertJsonMissingPath('data.smtp_password');
    }
}
