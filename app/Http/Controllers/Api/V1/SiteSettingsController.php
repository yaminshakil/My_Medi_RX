<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;

class SiteSettingsController extends Controller
{
    private const PUBLIC_SETTING_KEYS = [
        'site_title',
        'currency',
        'currency_symbol',
        'timezone',
        'site_country_code',
        'records_per_page',
        'currency_format',
        'logo_path',
        'navbar_logo_path',
        'dashboard_logo_path',
        'favicon_path',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_og_title',
        'seo_og_description',
        'seo_og_image_path',
        'seo_twitter_title',
        'seo_twitter_description',
        'seo_twitter_image_path',
        'site_base_color',
        'primary_menu_bgcolor',
        'site_button_bgcolor',
        'site_button_hover_bgcolor',
        'site_header_top_bgcolor',
        'site_footer_bgcolor',
        'site_footer_bottom_bgcolor',
        'site_mobile',
        'site_email',
        'site_address',
    ];

    public function __invoke(): JsonResponse
    {
        $storedSettings = Setting::query()
            ->whereIn('meta_key', self::PUBLIC_SETTING_KEYS)
            ->get(['meta_key', 'meta_value'])
            ->mapWithKeys(fn (Setting $setting): array => [
                $setting->meta_key => $setting->getRawOriginal('meta_value'),
            ]);

        $settings = collect(self::PUBLIC_SETTING_KEYS)
            ->mapWithKeys(fn (string $key): array => [$key => $storedSettings->get($key)]);

        return ApiResponseService::success($settings, 'Site settings retrieved successfully.');
    }
}
