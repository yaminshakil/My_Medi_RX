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
            'edit_menu_description' => [
                'en' => 'Update an existing navigation menu item',
                'bn' => 'বিদ্যমান নেভিগেশন মেনু আইটেম আপডেট করুন',
            ],
            'back' => [
                'en' => 'Back',
                'bn' => 'ফিরে যান',
            ],
            'create' => [
                'en' => 'Create',
                'bn' => 'তৈরি করুন',
            ],
            'cancel' => [
                'en' => 'Cancel',
                'bn' => 'বাতিল',
            ],
            'name' => [
                'en' => 'Name',
                'bn' => 'নাম',
            ],
            'Translations' => [
                'en' => 'Translations',
                'bn' => 'অনুবাদ',
            ],
            'Enter_the_specialty_name_in_each_supported_language' => [
                'en' => 'Enter the specialty name in each supported language.',
                'bn' => 'প্রতিটি সমর্থিত ভাষায় বিশেষত্বের নাম লিখুন।',
            ],
            'slug' => [
                'en' => 'Slug',
                'bn' => 'স্লাগ',
            ],
            'icon' => [
                'en' => 'Icon',
                'bn' => 'আইকন',
            ],
            'lucid_icon_name' => [
                'en' => 'Lucid Icon Name',
                'bn' => 'লুসিড আইকন নাম',
            ],
            'order' => [
                'en' => 'Order',
                'bn' => 'ক্রম',
            ],
            'parent_menu' => [
                'en' => 'Parent Menu',
                'bn' => 'প্রধান মেনু',
            ],
            'role' => [
                'en' => 'Role',
                'bn' => 'ভূমিকা',
            ],
            'profile_settings' => [
                'en' => 'Profile settings',
                'bn' => 'প্রোফাইল সেটিংস',
            ],
            'logout' => [
                'en' => 'Log Out',
                'bn' => 'লগ আউট',
            ],
            'settings' => [
                'en' => 'Settings',
                'bn' => 'সেটিংস',
            ],
            'settings_description' => [
                'en' => 'Manage your profile and account settings',
                'bn' => 'আপনার প্রোফাইল ও অ্যাকাউন্টের সেটিংস পরিচালনা করুন',
            ],
            'Profile' => [
                'en' => 'Profile',
                'bn' => 'প্রোফাইল',
            ],
            'profile_information' => [
                'en' => 'Profile information',
                'bn' => 'প্রোফাইল তথ্য',
            ],
            'profile_information_sub' => [
                'en' => 'Update your name and email address',
                'bn' => 'আপনার নাম এবং ইমেইল ঠিকানা আপডেট করুন',
            ],
            'email_address' => [
                'en' => 'Email address',
                'bn' => 'ইমেইল ঠিকানা',
            ],
            'delete_account' => [
                'en' => 'Delete account',
                'bn' => 'অ্যাকাউন্ট মুছে ফেলুন',
            ],
            'system_settings' => [
                'en' => 'System Settings',
                'bn' => 'সিস্টেম সেটিংস',
            ],
            'general_settings' => [
                'en' => 'General Settings',
                'bn' => 'সাধারণ সেটিংস',
            ],
            'site_identity' => [
                'en' => 'Site Identity',
                'bn' => 'সাইট পরিচিতি',
            ],
            'color_settings' => [
                'en' => 'Color Settings',
                'bn' => 'রঙের সেটিংস',
            ],
            'system_configuration' => [
                'en' => 'System Configuration',
                'bn' => 'সিস্টেম কনফিগারেশন',
            ],
            'notification_settings' => [
                'en' => 'Notification Setting',
                'bn' => 'নোটিফিকেশন সেটিং',
            ],
            'seo_configuration' => [
                'en' => 'SEO Configuration',
                'bn' => 'এসইও কনফিগারেশন',
            ],
            'configure' => [
                'en' => 'Configure',
                'bn' => 'কনফিগার করুন',
            ],
            'here' => [
                'en' => 'here',
                'bn' => 'এখানে',
            ],
            'site_title' => [
                'en' => 'Site Title',
                'bn' => 'সাইট টাইটেল',
            ],
            'currency' => [
                'en' => 'Currency',
                'bn' => 'মুদ্রা',
            ],
            'currency_symbol' => [
                'en' => 'Currency Symbol',
                'bn' => 'মুদ্রা সংকেত',
            ],
            'currency_format' => [
                'en' => 'Currency Format',
                'bn' => 'মুদ্রা ফরম্যাট',
            ],
            'timezone' => [
                'en' => 'Timezone',
                'bn' => 'টাইমজোন',
            ],
            'mobile_country_code' => [
                'en' => 'Mobile Country Code',
                'bn' => 'মোবাইল কাউন্ট্রি কোড',
            ],
            'records_per_page' => [
                'en' => 'Records Per Page',
                'bn' => 'প্রতি পৃষ্ঠায় রেকর্ডস',
            ],
            'mobile' => [
                'en' => 'Mobile',
                'bn' => 'মোবাইল',
            ],
            'email' => [
                'en' => 'Email',
                'bn' => 'ইমেইল',
            ],
            'site_address' => [
                'en' => 'Site Address',
                'bn' => 'সাইট ঠিকানা',
            ],
            'logo' => [
                'en' => 'Logo',
                'bn' => 'লোগো',
            ],
            'navbar_logo' => [
                'en' => 'Navbar Logo',
                'bn' => 'নাভিগেশন লোগো',
            ],
            'dashboard_logo' => [
                'en' => 'Dashboard Logo',
                'bn' => 'ড্যাশবোর্ড লোগো',
            ],
            'favicon' => [
                'en' => 'Favicon',
                'bn' => 'ফেভিকন',
            ],
            'site_base_color' => [
                'en' => 'Site Base Color',
                'bn' => 'সাইট বেস রঙ',
            ],
            'site_button_bgcolor' => [
                'en' => 'Site Button Color',
                'bn' => 'সাইট বাটন রঙ',
            ],
            'site_button_hover_bgcolor' => [
                'en' => 'Site Button Hover Color',
                'bn' => 'সাইট বাটন রঙ',
            ],
            'site_header_top_bgcolor' => [
                'en' => 'Site Header Top Color',
                'bn' => 'সাইট হেডার টপ রঙ',
            ],
            'site_footer_bgcolor' => [
                'en' => 'Site Footer Color',
                'bn' => 'সাইট ফুটার রঙ',
            ],
            'site_footer_bottom_bgcolor' => [
                'en' => 'Site Footer Bottom Color',
                'bn' => 'সাইট ফুটার নিচের রঙ',
            ],
        ];

        $now = now();

        foreach ($translations as $key => $locales) {
            foreach ($locales as $locale => $value) {
                DB::table('translations')->updateOrInsert(
                    [
                        'group'             => 'common',
                        'key'               => $key,
                        'locale'            => $locale,
                        'translatable_type' => null,
                        'translatable_id'   => null,
                        'field'             => null,
                    ],
                    [
                        'value'      => $value,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}
