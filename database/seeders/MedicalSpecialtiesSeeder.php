<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicalSpecialtiesSeeder extends Seeder
{
    public function run(): void
    {
        // DB::table('medical_specialties')->truncate();

        // Helper to insert and return ID
        $insert = function ($name, $parentId = null, $isSurgical = false, $icon = null, $description = null) {
            return DB::table('medical_specialties')->insertGetId([
                'name'        => $name,
                'icon'        => $icon ?? 'stethoscope', // default icon
                'description' => $description ?? "Specialty in {$name}",
                'parent_id'   => $parentId,
                'is_surgical' => $isSurgical,
            ]);
        };

        // Top-level categories
        $medicineId = $insert('Medicine', null, false, 'activity', 'Covers internal medicine and its subspecialties');
        $surgeryId = $insert('Surgery', null, true, 'slice', 'All surgical specialties and subspecialties');
        $womensId = $insert("Women's Health", null, true, 'heart', 'Specialties focusing on female reproductive health');
        $pediatricsId = $insert('Pediatrics', null, false, 'baby', 'Medical care for infants, children, and adolescents');
        $anesthesiaId = $insert('Anesthesiology', null, false, 'pill', 'Specialty for anesthesia and pain management');
        $pathologyId = $insert('Pathology', null, false, 'dna', 'Study of disease through lab tests and analysis');
        $radiologyId = $insert('Radiology', null, false, 'scan-line', 'Imaging specialties including diagnostic and interventional');
        $psychiatryId = $insert('Psychiatry', null, false, 'brain', 'Mental health and psychiatric care');
        $eyeId = $insert('Eye & Vision', null, true, 'eye', 'Ophthalmology and related eye care specialties');
        $entId = $insert('Ear, Nose & Throat', null, true, 'ear', 'Otolaryngology and head & neck care');
        $dentalId = $insert('Dental & Oral Health', null, false, 'UserCog', 'Dentistry and oral health specialties');
        $publicId = $insert('Public & Preventive Health', null, false, 'shield-check', 'Community and preventive medicine');
        $emergencyId = $insert('Emergency & Critical Care', null, true, 'ambulance', 'Emergency medicine and ICU care');
        $familyId = $insert('Family & Community Medicine', null, false, 'users', 'Primary care for individuals and families');

        // Medicine subspecialties
        foreach ([
            'Internal Medicine (General)',
            'Cardiology',
            'Neurology',
            'Endocrinology',
            'Gastroenterology',
            'Nephrology',
            'Hematology',
            'Dermatology',
            'Infectious Diseases',
            'Oncology (Medical)',
            'Pulmonology',
        ] as $name) {
            $insert($name, $medicineId, false, 'stethoscope');
        }

        // Surgical specialties
        foreach ([
            'General Surgery',
            'Neurosurgery',
            'Orthopedic Surgery',
            'Urology',
            'Plastic & Reconstructive',
            'Pediatric Surgery',
            'Surgical Oncology',
        ] as $name) {
            $insert($name, $surgeryId, true, 'slice');
        }

        // Women's Health
        $insert('Obstetrics & Gynecology', $womensId, true, 'heart');
        $insert('Gynecologic Oncology', $womensId, true, 'hospital');
        $insert('Maternal–Fetal Medicine', $womensId, false, 'baby');

        // Pediatrics
        foreach ([
            'Pediatrics (General)',
            'Pediatric Cardiology',
            'Neonatology',
            'Pediatric Hematology/Oncology',
        ] as $name) {
            $insert($name, $pediatricsId, false, 'baby');
        }

        // Anesthesiology, Pathology, Radiology
        $insert('Anesthesiology (General)', $anesthesiaId, false, 'pill');
        $insert('Pathology (Anatomical)', $pathologyId, false, 'dna');
        $insert('Pathology (Clinical/Lab)', $pathologyId, false, 'dna');
        $insert('Radiology (Diagnostic)', $radiologyId, false, 'scan-line');
        $insert('Interventional Radiology', $radiologyId, true, 'scan-line');

        // Psychiatry
        $insert('Psychiatry (General)', $psychiatryId, false, 'brain');
        $insert('Child & Adolescent Psychiatry', $psychiatryId, false, 'smile');

        // Eye, ENT, Dental
        $insert('Ophthalmology', $eyeId, true, 'eye');
        $insert('Otolaryngology (ENT)', $entId, true, 'ear');
        $insert('Dentistry (General)', $dentalId, false, 'UserCog');
        $insert('Oral & Maxillofacial Surgery', $dentalId, true, 'UserCog');

        // Public Health, Emergency, Family
        $insert('Community Medicine', $publicId, false, 'shield-check');
        $insert('Emergency Medicine', $emergencyId, false, 'ambulance');
        $insert('Critical Care Medicine', $emergencyId, false, 'alert-triangle');
        $insert('Family Medicine', $familyId, false, 'users');
    }
}
