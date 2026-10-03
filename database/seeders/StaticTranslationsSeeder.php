<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaticTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'welcome' => [
                'en' => 'Welcome to MediRx',
                'bn' => 'মেডিরেক্সে স্বাগতম',
            ],
            'dashboard' => [
                'en' => 'Dashboard',
                'bn' => 'ড্যাশবোর্ড',
            ],
            'patients' => [
                'en' => 'Patients',
                'bn' => 'রোগী',
            ],
            'doctors' => [
                'en' => 'Doctors',
                'bn' => 'ডাক্তার',
            ],
            'appointments' => [
                'en' => 'Appointments',
                'bn' => 'অ্যাপয়েন্টমেন্ট',
            ],
            'save' => [
                'en' => 'Save',
                'bn' => 'সংরক্ষণ করুন',
            ],
            'update' => [
                'en' => 'Update',
                'bn' => 'আপডেট করুন',
            ],
            'delete' => [
                'en' => 'Delete',
                'bn' => 'মুছে ফেলুন',
            ],
            'medical_specialties' => [
                'en' => 'Medical Specialties',
                'bn' => 'চিকিৎসা বিশেষত্ব',
            ],
            'search_specialty' => [
                'en' => 'Search specialty...',
                'bn' => 'স্পেশালটি অনুসন্ধান করুন...',
            ],
            'delete_specialty' => [
                'en' => 'Delete Specialty',
                'bn' => 'স্পেশালটি মুছে ফেলুন',
            ],
            'no_specialty_found' => [
                'en' => 'No specialty found.',
                'bn' => 'কোন স্পেশালটি পাওয়া যায়নি।',
            ],
            'show' => [
                'en' => 'Show',
                'bn' => 'দেখান',
            ],
            'rows_per_page' => [
                'en' => 'Rows per page',
                'bn' => 'প্রতি পৃষ্ঠায় সারি',
            ],
            'No_entries_found' => [
                'en' => 'No entries found',
                'bn' => 'কোন এন্ট্রি পাওয়া যায়নি',
            ],
            'Showing' => [
                'en' => 'Showing',
                'bn' => 'দেখানো হচ্ছে',
            ],
            'to' => [
                'en' => 'to',
                'bn' => 'থেকে',
            ],
            'from_total' => [
                'en' => 'from total',
                'bn' => 'মোট থেকে',
            ],
            'entries' => [
                'en' => 'entries',
                'bn' => 'এন্ট্রি',
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
