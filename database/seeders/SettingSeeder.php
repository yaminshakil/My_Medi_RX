<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_title'                 => 'MediRx',
            'currency'                   => 'BDT',
            'currency_symbol'            => 'TK',
            'timezone'                   => 'Asia/Dhaka',
            'site_country_code'          => '+880',
            'records_per_page'           => '10',
            'currency_format'            => 'symbol_only',
            'logo_path'                  => 'default/logo.png',
            'navbar_logo_path'           => null,
            'dashboard_logo_path'        => 'default/dashboard_logo.png',
            'favicon_path'               => null,
            'seo_title'                  => 'MediRx',
            'seo_description'            => 'MediRx is a purpose-built Get digital prescriptions sent directly to your pharmacy. Our secure e-prescription service makes managing your medications simple, safe, and efficient. Sign up today.',
            'seo_keywords'               => 'MediRx digital solutions,  online prescription refill, how to get an e-prescription, e-pharmacy',
            'seo_og_title'               => 'MediRx',
            'seo_og_description'         => 'MediRx is a purpose-built Get digital prescriptions sent directly to your pharmacy. Our secure e-prescription service makes managing your medications simple, safe, and efficient. Sign up today.',
            'seo_og_image_path'          => '',
            'seo_twitter_title'          => 'MediRx',
            'seo_twitter_description'    => 'MediRx is a purpose-built Get digital prescriptions sent directly to your pharmacy. Our secure e-prescription service makes managing your medications simple, safe, and efficient. Sign up today.',
            'seo_twitter_image_path'     => '',
            'site_base_color'            => '#1656ad',
            'primary_menu_bgcolor'       => '#852ba6',
            'site_button_bgcolor'        => '#4b80c7',
            'site_button_hover_bgcolor'  => '#1656ad',
            'site_header_top_bgcolor'    => '#343b54',
            'site_footer_bgcolor'        => '#334461',
            'site_footer_bottom_bgcolor' => '#343b54',
            'site_mobile'                => '123456789',
            'site_email'                 => 'info@nsbd.net',
            'site_address'               => 'Kishoregonj, Dhaka, Bangladesh',
        ];

        foreach ($defaults as $key => $value) {
            Setting::setValue($key, $value);
        }
    }
}
