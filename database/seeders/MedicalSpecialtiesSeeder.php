<?php

namespace Database\Seeders;

use App\Models\MedicalSpecialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicalSpecialtiesSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Helper: Create Specialty + English/Bangla Translations
        |--------------------------------------------------------------------------
        */

        $insert = function (
            string $name,
            ?int $parentId = null,
            bool $isSurgical = false,
            ?string $icon = null,
            ?string $description = null,
            ?string $banglaName = null,
            ?string $banglaDescription = null
        ) {
            $description ??= "Specialty in {$name}";

            // Create medical specialty
            $specialtyId = DB::table('medical_specialties')->insertGetId([
                'name'        => $name,
                'icon'        => $icon ?? 'stethoscope',
                'description' => $description,
                'parent_id'   => $parentId,
                'is_surgical' => $isSurgical,
            ]);

            /*
            |--------------------------------------------------------------------------
            | English Name
            |--------------------------------------------------------------------------
            */

            DB::table('translations')->insert([
                'translatable_type' => MedicalSpecialty::class,
                'translatable_id'   => $specialtyId,
                'group'             => null,
                'key'               => null,
                'field'             => 'name',
                'locale'            => 'en',
                'value'             => $name,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Bangla Name
            |--------------------------------------------------------------------------
            */

            if ($banglaName) {
                DB::table('translations')->insert([
                    'translatable_type' => MedicalSpecialty::class,
                    'translatable_id'   => $specialtyId,
                    'group'             => null,
                    'key'               => null,
                    'field'             => 'name',
                    'locale'            => 'bn',
                    'value'             => $banglaName,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | English Description
            |--------------------------------------------------------------------------
            */

            DB::table('translations')->insert([
                'translatable_type' => MedicalSpecialty::class,
                'translatable_id'   => $specialtyId,
                'group'             => null,
                'key'               => null,
                'field'              => 'description',
                'locale'             => 'en',
                'value'              => $description,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Bangla Description
            |--------------------------------------------------------------------------
            */

            if ($banglaDescription) {
                DB::table('translations')->insert([
                    'translatable_type' => MedicalSpecialty::class,
                    'translatable_id'   => $specialtyId,
                    'group'             => null,
                    'key'               => null,
                    'field'              => 'description',
                    'locale'            => 'bn',
                    'value'             => $banglaDescription,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            return $specialtyId;
        };

        /*
        |--------------------------------------------------------------------------
        | Top-Level Categories
        |--------------------------------------------------------------------------
        */

        $medicineId = $insert(
            'Medicine',
            null,
            false,
            'activity',
            'Covers internal medicine and its subspecialties',
            'মেডিসিন',
            'অভ্যন্তরীণ মেডিসিন এবং এর উপ-বিশেষায়িত চিকিৎসা ক্ষেত্রসমূহ'
        );

        $surgeryId = $insert(
            'Surgery',
            null,
            true,
            'slice',
            'All surgical specialties and subspecialties',
            'সার্জারি',
            'সকল সার্জিক্যাল বিশেষায়িত চিকিৎসা ও উপ-বিশেষায়িত চিকিৎসা ক্ষেত্রসমূহ'
        );

        $womensId = $insert(
            "Women's Health",
            null,
            true,
            'heart',
            'Specialties focusing on female reproductive health',
            'নারী স্বাস্থ্য',
            'নারীর প্রজনন ও সংশ্লিষ্ট স্বাস্থ্যসেবাকেন্দ্রিক বিশেষায়িত চিকিৎসা'
        );

        $pediatricsId = $insert(
            'Pediatrics',
            null,
            false,
            'baby',
            'Medical care for infants, children, and adolescents',
            'শিশু রোগবিদ্যা',
            'শিশু, নবজাতক ও কিশোর-কিশোরীদের চিকিৎসাসেবা'
        );

        $anesthesiaId = $insert(
            'Anesthesiology',
            null,
            false,
            'pill',
            'Specialty for anesthesia and pain management',
            'অ্যানেসথেসিওলজি',
            'অ্যানেসথেশিয়া ও ব্যথা ব্যবস্থাপনা সম্পর্কিত বিশেষায়িত চিকিৎসা'
        );

        $pathologyId = $insert(
            'Pathology',
            null,
            false,
            'dna',
            'Study of disease through laboratory tests and analysis',
            'প্যাথলজি',
            'ল্যাবরেটরি পরীক্ষা ও বিশ্লেষণের মাধ্যমে রোগ নির্ণয় ও গবেষণা'
        );

        $radiologyId = $insert(
            'Radiology',
            null,
            false,
            'scan-line',
            'Imaging specialties including diagnostic and interventional radiology',
            'রেডিওলজি',
            'ডায়াগনস্টিক ও ইন্টারভেনশনাল রেডিওলজিসহ ইমেজিংভিত্তিক বিশেষায়িত চিকিৎসা'
        );

        $psychiatryId = $insert(
            'Psychiatry',
            null,
            false,
            'brain',
            'Mental health and psychiatric care',
            'মনোরোগবিদ্যা',
            'মানসিক স্বাস্থ্য ও মনোরোগ চিকিৎসা'
        );

        $eyeId = $insert(
            'Eye & Vision',
            null,
            true,
            'eye',
            'Ophthalmology and related eye care specialties',
            'চক্ষু ও দৃষ্টি',
            'চক্ষুবিদ্যা ও সংশ্লিষ্ট চোখের চিকিৎসাসেবা'
        );

        $entId = $insert(
            'Ear, Nose & Throat',
            null,
            true,
            'ear',
            'Otolaryngology and head & neck care',
            'কান, নাক ও গলা',
            'কান, নাক, গলা ও মাথা-ঘাড়ের চিকিৎসাসেবা'
        );

        $dentalId = $insert(
            'Dental & Oral Health',
            null,
            false,
            'UserCog',
            'Dentistry and oral health specialties',
            'দন্ত ও মুখের স্বাস্থ্য',
            'দাঁত, মাড়ি ও মুখের স্বাস্থ্য সম্পর্কিত বিশেষায়িত চিকিৎসা'
        );

        $publicId = $insert(
            'Public & Preventive Health',
            null,
            false,
            'shield-check',
            'Community and preventive medicine',
            'জনস্বাস্থ্য ও প্রতিরোধমূলক স্বাস্থ্য',
            'জনস্বাস্থ্য, রোগ প্রতিরোধ ও কমিউনিটি স্বাস্থ্যসেবা'
        );

        $emergencyId = $insert(
            'Emergency & Critical Care',
            null,
            true,
            'ambulance',
            'Emergency medicine and ICU care',
            'জরুরি ও ক্রিটিক্যাল কেয়ার',
            'জরুরি চিকিৎসা ও নিবিড় পরিচর্যা কেন্দ্রিক চিকিৎসাসেবা'
        );

        $familyId = $insert(
            'Family & Community Medicine',
            null,
            false,
            'users',
            'Primary care for individuals and families',
            'ফ্যামিলি ও কমিউনিটি মেডিসিন',
            'ব্যক্তি ও পরিবারের প্রাথমিক এবং কমিউনিটি স্বাস্থ্যসেবা'
        );

        /*
        |--------------------------------------------------------------------------
        | Medicine Subspecialties
        |--------------------------------------------------------------------------
        */

        $medicineSpecialties = [
            'Internal Medicine (General)' => [
                'name' => 'সাধারণ অভ্যন্তরীণ মেডিসিন',
                'description' => 'অভ্যন্তরীণ রোগের সাধারণ চিকিৎসা ও ব্যবস্থাপনা',
            ],
            'Cardiology' => [
                'name' => 'কার্ডিওলজি',
                'description' => 'হৃদপিণ্ড ও রক্তনালীর রোগের চিকিৎসা',
            ],
            'Neurology' => [
                'name' => 'নিউরোলজি',
                'description' => 'মস্তিষ্ক, স্নায়ু ও স্নায়ুতন্ত্রের রোগের চিকিৎসা',
            ],
            'Endocrinology' => [
                'name' => 'এন্ডোক্রাইনোলজি',
                'description' => 'হরমোন ও অন্তঃক্ষরা গ্রন্থির রোগের চিকিৎসা',
            ],
            'Gastroenterology' => [
                'name' => 'গ্যাস্ট্রোএন্টারোলজি',
                'description' => 'পরিপাকতন্ত্র ও সংশ্লিষ্ট অঙ্গের রোগের চিকিৎসা',
            ],
            'Nephrology' => [
                'name' => 'নেফ্রোলজি',
                'description' => 'কিডনি ও কিডনি-সংক্রান্ত রোগের চিকিৎসা',
            ],
            'Hematology' => [
                'name' => 'হেমাটোলজি',
                'description' => 'রক্ত ও রক্তসংক্রান্ত রোগের চিকিৎসা',
            ],
            'Dermatology' => [
                'name' => 'ডার্মাটোলজি',
                'description' => 'ত্বক, চুল ও নখের রোগের চিকিৎসা',
            ],
            'Infectious Diseases' => [
                'name' => 'সংক্রামক রোগ',
                'description' => 'সংক্রমণজনিত রোগের নির্ণয় ও চিকিৎসা',
            ],
            'Oncology (Medical)' => [
                'name' => 'মেডিকেল অনকোলজি',
                'description' => 'ক্যান্সার ও টিউমার সম্পর্কিত চিকিৎসা',
            ],
            'Pulmonology' => [
                'name' => 'পালমোনোলজি',
                'description' => 'ফুসফুস ও শ্বাসতন্ত্রের রোগের চিকিৎসা',
            ],
        ];

        foreach ($medicineSpecialties as $name => $translation) {
            $insert(
                $name,
                $medicineId,
                false,
                'stethoscope',
                null,
                $translation['name'],
                $translation['description']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Surgical Specialties
        |--------------------------------------------------------------------------
        */

        $surgicalSpecialties = [
            'General Surgery' => [
                'name' => 'জেনারেল সার্জারি',
                'description' => 'বিভিন্ন সাধারণ রোগের সার্জিক্যাল চিকিৎসা',
            ],
            'Neurosurgery' => [
                'name' => 'নিউরোসার্জারি',
                'description' => 'মস্তিষ্ক, মেরুদণ্ড ও স্নায়ুতন্ত্রের সার্জারি',
            ],
            'Orthopedic Surgery' => [
                'name' => 'অর্থোপেডিক সার্জারি',
                'description' => 'হাড়, জয়েন্ট ও মাংসপেশির সার্জিক্যাল চিকিৎসা',
            ],
            'Urology' => [
                'name' => 'ইউরোলজি',
                'description' => 'মূত্রনালী ও পুরুষ প্রজননতন্ত্রের রোগের চিকিৎসা',
            ],
            'Plastic & Reconstructive' => [
                'name' => 'প্লাস্টিক ও পুনর্গঠনমূলক সার্জারি',
                'description' => 'দেহের গঠন ও ক্ষত পুনর্গঠনের সার্জারি',
            ],
            'Pediatric Surgery' => [
                'name' => 'পেডিয়াট্রিক সার্জারি',
                'description' => 'শিশুদের বিভিন্ন রোগের সার্জিক্যাল চিকিৎসা',
            ],
            'Surgical Oncology' => [
                'name' => 'সার্জিক্যাল অনকোলজি',
                'description' => 'ক্যান্সারের সার্জিক্যাল চিকিৎসা',
            ],
        ];

        foreach ($surgicalSpecialties as $name => $translation) {
            $insert(
                $name,
                $surgeryId,
                true,
                'slice',
                null,
                $translation['name'],
                $translation['description']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Women's Health
        |--------------------------------------------------------------------------
        */

        $insert(
            'Obstetrics & Gynecology',
            $womensId,
            true,
            'heart',
            null,
            'প্রসূতি ও স্ত্রীরোগবিদ্যা',
            'গর্ভাবস্থা, প্রসব ও নারীর প্রজননস্বাস্থ্যের চিকিৎসা'
        );

        $insert(
            'Gynecologic Oncology',
            $womensId,
            true,
            'hospital',
            null,
            'গাইনোকোলজিক্যাল অনকোলজি',
            'নারীর প্রজনন অঙ্গের ক্যান্সারের চিকিৎসা'
        );

        $insert(
            'Maternal–Fetal Medicine',
            $womensId,
            false,
            'baby',
            null,
            'মাতৃ-ভ্রূণ চিকিৎসা',
            'গর্ভাবস্থায় মা ও ভ্রূণের জটিলতার বিশেষায়িত চিকিৎসা'
        );

        /*
        |--------------------------------------------------------------------------
        | Pediatrics
        |--------------------------------------------------------------------------
        */

        $pediatricSpecialties = [
            'Pediatrics (General)' => [
                'name' => 'সাধারণ শিশু রোগবিদ্যা',
                'description' => 'শিশুদের সাধারণ রোগের চিকিৎসা ও ব্যবস্থাপনা',
            ],
            'Pediatric Cardiology' => [
                'name' => 'পেডিয়াট্রিক কার্ডিওলজি',
                'description' => 'শিশুদের হৃদরোগের বিশেষায়িত চিকিৎসা',
            ],
            'Neonatology' => [
                'name' => 'নিওনেটোলজি',
                'description' => 'নবজাতক শিশুদের বিশেষায়িত চিকিৎসা',
            ],
            'Pediatric Hematology/Oncology' => [
                'name' => 'পেডিয়াট্রিক হেমাটোলজি/অনকোলজি',
                'description' => 'শিশুদের রক্তরোগ ও ক্যান্সারের চিকিৎসা',
            ],
        ];

        foreach ($pediatricSpecialties as $name => $translation) {
            $insert(
                $name,
                $pediatricsId,
                false,
                'baby',
                null,
                $translation['name'],
                $translation['description']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Anesthesiology, Pathology & Radiology
        |--------------------------------------------------------------------------
        */

        $insert(
            'Anesthesiology (General)',
            $anesthesiaId,
            false,
            'pill',
            null,
            'সাধারণ অ্যানেসথেসিওলজি',
            'অ্যানেসথেশিয়া প্রদান ও রোগীর ব্যথা ব্যবস্থাপনার সাধারণ চিকিৎসা'
        );

        $insert(
            'Pathology (Anatomical)',
            $pathologyId,
            false,
            'dna',
            null,
            'অ্যানাটমিক্যাল প্যাথলজি',
            'টিস্যু ও কোষের পরীক্ষার মাধ্যমে রোগ নির্ণয়'
        );

        $insert(
            'Pathology (Clinical/Lab)',
            $pathologyId,
            false,
            'dna',
            null,
            'ক্লিনিক্যাল/ল্যাবরেটরি প্যাথলজি',
            'ল্যাবরেটরি পরীক্ষার মাধ্যমে রোগ নির্ণয় ও বিশ্লেষণ'
        );

        $insert(
            'Radiology (Diagnostic)',
            $radiologyId,
            false,
            'scan-line',
            null,
            'ডায়াগনস্টিক রেডিওলজি',
            'বিভিন্ন ইমেজিং পদ্ধতির মাধ্যমে রোগ নির্ণয়'
        );

        $insert(
            'Interventional Radiology',
            $radiologyId,
            true,
            'scan-line',
            null,
            'ইন্টারভেনশনাল রেডিওলজি',
            'ইমেজিংয়ের সহায়তায় ন্যূনতম আক্রমণাত্মক চিকিৎসা পদ্ধতি'
        );

        /*
        |--------------------------------------------------------------------------
        | Psychiatry
        |--------------------------------------------------------------------------
        */

        $insert(
            'Psychiatry (General)',
            $psychiatryId,
            false,
            'brain',
            null,
            'সাধারণ মনোরোগবিদ্যা',
            'মানসিক ও আচরণগত সমস্যার সাধারণ চিকিৎসা'
        );

        $insert(
            'Child & Adolescent Psychiatry',
            $psychiatryId,
            false,
            'smile',
            null,
            'শিশু ও কিশোর মনোরোগবিদ্যা',
            'শিশু ও কিশোর-কিশোরীদের মানসিক ও আচরণগত সমস্যার চিকিৎসা'
        );

        /*
        |--------------------------------------------------------------------------
        | Eye, ENT & Dental
        |--------------------------------------------------------------------------
        */

        $insert(
            'Ophthalmology',
            $eyeId,
            true,
            'eye',
            null,
            'চক্ষুবিদ্যা',
            'চোখ ও দৃষ্টিসংক্রান্ত রোগের চিকিৎসা'
        );

        $insert(
            'Otolaryngology (ENT)',
            $entId,
            true,
            'ear',
            null,
            'অটোল্যারিঙ্গোলজি (ENT)',
            'কান, নাক ও গলার রোগের চিকিৎসা'
        );

        $insert(
            'Dentistry (General)',
            $dentalId,
            false,
            'UserCog',
            null,
            'সাধারণ দন্তচিকিৎসা',
            'দাঁত, মাড়ি ও মুখের সাধারণ রোগের চিকিৎসা'
        );

        $insert(
            'Oral & Maxillofacial Surgery',
            $dentalId,
            true,
            'UserCog',
            null,
            'ওরাল ও ম্যাক্সিলোফেসিয়াল সার্জারি',
            'মুখ, চোয়াল ও মুখমণ্ডলের সার্জিক্যাল চিকিৎসা'
        );

        /*
        |--------------------------------------------------------------------------
        | Public Health, Emergency & Family Medicine
        |--------------------------------------------------------------------------
        */

        $insert(
            'Community Medicine',
            $publicId,
            false,
            'shield-check',
            null,
            'কমিউনিটি মেডিসিন',
            'জনগোষ্ঠীর স্বাস্থ্য ও রোগ প্রতিরোধ সম্পর্কিত চিকিৎসাবিজ্ঞান'
        );

        $insert(
            'Emergency Medicine',
            $emergencyId,
            false,
            'ambulance',
            null,
            'ইমার্জেন্সি মেডিসিন',
            'জরুরি ও তাৎক্ষণিক চিকিৎসাসেবা'
        );

        $insert(
            'Critical Care Medicine',
            $emergencyId,
            false,
            'alert-triangle',
            null,
            'ক্রিটিক্যাল কেয়ার মেডিসিন',
            'গুরুতর অসুস্থ রোগীদের নিবিড় চিকিৎসা ও পর্যবেক্ষণ'
        );

        $insert(
            'Family Medicine',
            $familyId,
            false,
            'users',
            null,
            'ফ্যামিলি মেডিসিন',
            'ব্যক্তি ও পরিবারের প্রাথমিক ও ধারাবাহিক স্বাস্থ্যসেবা'
        );
    }
}
