<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaticTestSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'menus' => [
                'en' => 'Menus',
                'bn' => 'মেনু',
            ],
            'menus_desc' => [
                'en' => 'Manage navigation menu items and order',
                'bn' => 'নেভিগেশন মেনুর আইটেম ও ক্রম পরিচালনা করুন',
            ],
            'create_menu' => [
                'en' => 'Create Menu',
                'bn' => 'মেনু তৈরি করুন',
            ],
            'create_menu_desc' => [
                'en' => 'Add a new navigation menu item to your application.',
                'bn' => 'আপনার এপ্লিকেশনে একটি নতুন নেভিগেশন মেনু আইটেম যোগ করুন।',
            ],
            'edit_menu' => [
                'en' => 'Edit Menu',
                'bn' => 'মেনু সম্পাদনা',
            ],
        ];

        $now = now();

        foreach ($translations as $key => $locales) {
            foreach ($locales as $locale => $value) {
                DB::table('translations')->updateOrInsert(
                    [
                        'group' => 'common',
                        'key' => $key,
                        'locale' => $locale,
                        'translatable_type' => null,
                        'translatable_id' => null,
                        'field' => null,
                    ],
                    [
                        'value' => $value,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}
