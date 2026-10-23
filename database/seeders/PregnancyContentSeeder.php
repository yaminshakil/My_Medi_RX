<?php

namespace Database\Seeders;

use App\Models\PregnancyTip;
use App\Models\PregnancyWeeklyHighlight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PregnancyContentSeeder extends Seeder
{
    private const SOURCE = 'medirx-weekly-seed-v1';

    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->highlights() as $item) {
                PregnancyWeeklyHighlight::updateOrCreate(
                    ['week' => $item['week']],
                    [
                        ...$item,
                        'source_reference' => self::SOURCE,
                        'source_version' => '1.0',
                        'is_published' => true,
                    ]
                );
            }

            foreach ($this->tips() as $week => $tip) {
                $weekNumber = $week + 1;

                PregnancyTip::updateOrCreate(
                    [
                        'source_reference' => self::SOURCE,
                        'week_start' => $weekNumber,
                        'week_end' => $weekNumber,
                    ],
                    [
                        'title' => $tip['title'],
                        'description' => $tip['description'],
                        'icon' => $tip['icon'],
                        'color_hex' => $tip['color_hex'],
                        'trimester' => $this->trimester($weekNumber),
                        'week_start' => $weekNumber,
                        'week_end' => $weekNumber,
                        'sort_order' => 1,
                        'is_published' => true,
                    ]
                );
            }
        });

        $this->command?->info(
            'Pregnancy content seeded: 40 highlights and 40 weekly tips.'
        );
    }

    private function trimester(int $week): int
    {
        return match (true) {
            $week <= 13 => 1,
            $week <= 27 => 2,
            default => 3,
        };
    }

    /**
     * Format:
     * week, fruit name, approximate length in cm,
     * approximate weight in grams, description.
     *
     * Values are illustrative and must be clinically reviewed.
     */
    private function highlights(): array
    {
        $rows = [
            [1,  'Not yet measurable', null, null, 'Pregnancy dating starts from the first day of the last menstrual period.'],
            [2,  'Not yet measurable', null, null, 'This is generally part of the menstrual cycle before conception.'],
            [3,  'Early embryo', null, null, 'Fertilization and early cell development may occur around this time.'],
            [4,  'Poppy seed', 0.1, null, 'Early development begins. Individual timing varies.'],
            [5,  'Sesame seed', 0.2, null, 'Early structures that contribute to the brain and spinal cord are developing.'],
            [6,  'Lentil', 0.5, null, 'Early organs and body structures continue to develop.'],
            [7,  'Blueberry', 1.0, null, 'The embryo continues developing, including early limb structures.'],
            [8,  'Raspberry', 1.6, 1, 'The embryo is growing rapidly and developing more recognizable features.'],
            [9,  'Cherry', 2.3, 2, 'The embryo is entering the fetal stage of development.'],
            [10, 'Strawberry', 3.1, 4, 'Major body structures are developing, though growth and maturation continue.'],
            [11, 'Fig', 4.1, 7, 'The fetus continues growing and developing its body structures.'],
            [12, 'Lime', 5.4, 14, 'The fetus is growing, and prenatal care remains important.'],
            [13, 'Peapod', 7.4, 23, 'The first trimester is nearing completion under the usual dating convention.'],
            [14, 'Lemon', 8.7, 43, 'The second trimester begins under the usual week-based convention.'],
            [15, 'Apple', 10.1, 70, 'Growth and development continue, including bones and movement.'],
            [16, 'Avocado', 11.6, 100, 'The fetus continues to grow and develop coordination.'],
            [17, 'Pear', 13.0, 140, 'Growth continues, and the body proportions are changing.'],
            [18, 'Bell pepper', 14.2, 190, 'The fetus continues developing sensory and movement-related structures.'],
            [19, 'Mango', 15.3, 240, 'Growth continues; some pregnant people begin to notice movement around this period.'],
            [20, 'Banana', 25.6, 300, 'Around the midpoint of pregnancy, the anatomy scan may be offered according to local care guidance.'],
            [21, 'Carrot', 26.7, 360, 'The fetus continues growing, and movements may become more noticeable.'],
            [22, 'Papaya', 27.8, 430, 'The senses and body systems continue to mature.'],
            [23, 'Grapefruit', 28.9, 500, 'The fetus continues to gain weight and develop.'],
            [24, 'Corn cob', 30.0, 600, 'Lung and other organ development continues, but substantial maturation remains.'],
            [25, 'Cauliflower', 34.6, 660, 'The fetus continues to gain weight and develop its response to sounds.'],
            [26, 'Lettuce', 35.6, 760, 'Growth continues, and the lungs and nervous system continue maturing.'],
            [27, 'Broccoli', 36.6, 875, 'The third trimester is approaching under the usual dating convention.'],
            [28, 'Eggplant', 37.6, 1000, 'The third trimester begins under the usual week-based convention.'],
            [29, 'Butternut squash', 38.6, 1150, 'The fetus continues to gain weight and develop muscle and bone.'],
            [30, 'Cabbage', 39.9, 1300, 'Brain development and weight gain continue.'],
            [31, 'Coconut', 41.1, 1500, 'The fetus continues growing and developing, with movements still important to monitor.'],
            [32, 'Squash', 42.4, 1700, 'The fetus continues gaining weight as the organs mature.'],
            [33, 'Pineapple', 43.7, 1900, 'The fetus continues developing and preparing for life after birth.'],
            [34, 'Cantaloupe', 45.0, 2150, 'The lungs and other systems continue maturing.'],
            [35, 'Honeydew melon', 46.2, 2400, 'The fetus continues to gain weight and may change position.'],
            [36, 'Romaine lettuce', 47.4, 2600, 'The fetus continues growing as the pregnancy approaches term.'],
            [37, 'Swiss chard', 48.6, 2900, 'This is considered early term; contact your maternity team with questions about labour signs.'],
            [38, 'Leek', 49.8, 3100, 'The fetus continues gaining weight and preparing for birth.'],
            [39, 'Mini watermelon', 50.7, 3300, 'This is considered full term under common obstetric definitions.'],
            [40, 'Small pumpkin', 51.2, 3500, 'The estimated due date is around this week, but actual birth timing varies.'],
        ];

        return array_map(
            fn (array $row) => [
                'week' => $row[0],
                'fruit_name' => $row[1],
                'approx_length_cm' => $row[2],
                'approx_weight_grams' => $row[3],
                'description' => $row[4],
            ],
            $rows
        );
    }

    /**
     * One reusable educational tip for each pregnancy week.
     * Content is general education, not individual medical advice.
     */
    private function tips(): array
    {
        return [
            ['title' => 'Start prenatal care', 'description' => 'Arrange prenatal care with a qualified healthcare professional and share your medical history and medicines.', 'icon' => 'calendar-check', 'color_hex' => '#16A34A'],
            ['title' => 'Review your health history', 'description' => 'Discuss existing conditions, previous pregnancies, supplements, and medicines with your clinician.', 'icon' => 'clipboard-list', 'color_hex' => '#0D9488'],
            ['title' => 'Confirm your next steps', 'description' => 'Ask your healthcare professional when to test, book appointments, and begin pregnancy care.', 'icon' => 'stethoscope', 'color_hex' => '#2563EB'],
            ['title' => 'Discuss early pregnancy symptoms', 'description' => 'Ask your clinician how to manage common symptoms and which warning signs need urgent assessment.', 'icon' => 'heart-pulse', 'color_hex' => '#DC2626'],
            ['title' => 'Ask about folic acid', 'description' => 'Discuss folic acid and appropriate prenatal supplements with your healthcare professional.', 'icon' => 'pill', 'color_hex' => '#7C3AED'],
            ['title' => 'Keep fluids within reach', 'description' => 'Drink fluids regularly and ask your clinician for advice if nausea or vomiting makes hydration difficult.', 'icon' => 'glass-water', 'color_hex' => '#0284C7'],
            ['title' => 'Plan your first appointments', 'description' => 'Keep your prenatal appointments and ask which screening tests are appropriate for you.', 'icon' => 'calendar-days', 'color_hex' => '#16A34A'],
            ['title' => 'Review medicines and supplements', 'description' => 'Check prescription medicines, over-the-counter products, and supplements with your clinician; do not stop prescribed medicines without advice.', 'icon' => 'pill', 'color_hex' => '#7C3AED'],
            ['title' => 'Support rest and sleep', 'description' => 'Make time for rest and discuss persistent sleep problems or severe fatigue with your healthcare professional.', 'icon' => 'moon', 'color_hex' => '#6366F1'],
            ['title' => 'Choose balanced meals', 'description' => 'Aim for a varied diet and ask your care team about nutrition needs and foods to avoid during pregnancy.', 'icon' => 'utensils', 'color_hex' => '#16A34A'],
            ['title' => 'Keep your prenatal visit', 'description' => 'Use your appointment to discuss symptoms, test results, and any questions about your pregnancy.', 'icon' => 'calendar-check', 'color_hex' => '#2563EB'],
            ['title' => 'Ask about screening options', 'description' => 'Discuss available prenatal screening and diagnostic tests, including their benefits and limitations.', 'icon' => 'clipboard-check', 'color_hex' => '#0D9488'],
            ['title' => 'Prepare for the next trimester', 'description' => 'Review your care plan and ask which changes or symptoms should prompt you to contact your clinician.', 'icon' => 'book-open', 'color_hex' => '#7C3AED'],
            ['title' => 'Review second-trimester care', 'description' => 'Confirm upcoming visits and ask your clinician about recommended screening and ultrasound timing.', 'icon' => 'calendar-days', 'color_hex' => '#16A34A'],
            ['title' => 'Keep moving if advised', 'description' => 'Ask whether moderate physical activity is suitable for you and which activities you should avoid.', 'icon' => 'footprints', 'color_hex' => '#0D9488'],
            ['title' => 'Support comfortable posture', 'description' => 'Take breaks and adjust your posture for comfort; discuss persistent pain with your care team.', 'icon' => 'person-standing', 'color_hex' => '#2563EB'],
            ['title' => 'Talk about emotional wellbeing', 'description' => 'Share ongoing anxiety, low mood, or stress with someone you trust and your healthcare professional.', 'icon' => 'heart-handshake', 'color_hex' => '#7C3AED'],
            ['title' => 'Ask about fetal movement', 'description' => 'Ask your maternity team when movement may become noticeable and how they recommend monitoring it.', 'icon' => 'baby', 'color_hex' => '#DB2777'],
            ['title' => 'Review your anatomy scan', 'description' => 'If an anatomy ultrasound is planned, ask what it checks and how you will receive the results.', 'icon' => 'scan', 'color_hex' => '#2563EB'],
            ['title' => 'Discuss your mid-pregnancy plan', 'description' => 'Review scan results and ask about upcoming tests, symptoms, and your individual care plan.', 'icon' => 'clipboard-list', 'color_hex' => '#16A34A'],
            ['title' => 'Learn your maternity contact options', 'description' => 'Keep your maternity team’s contact details handy and know where to seek urgent pregnancy care.', 'icon' => 'phone', 'color_hex' => '#DC2626'],
            ['title' => 'Make time for rest', 'description' => 'Balance daily activities with rest and ask for advice if breathlessness, pain, or fatigue is persistent or severe.', 'icon' => 'moon', 'color_hex' => '#6366F1'],
            ['title' => 'Discuss changes in movement', 'description' => 'Learn your maternity team’s guidance for fetal movement. Seek prompt assessment if movement decreases or changes from the usual pattern.', 'icon' => 'baby', 'color_hex' => '#DC2626'],
            ['title' => 'Review warning signs', 'description' => 'Ask which symptoms require urgent care, including bleeding, fluid leakage, severe headache, or severe abdominal pain.', 'icon' => 'alert-circle', 'color_hex' => '#DC2626'],
            ['title' => 'Check your hydration and nutrition', 'description' => 'Maintain regular meals and fluids as tolerated, and ask your clinician about any dietary restrictions.', 'icon' => 'glass-water', 'color_hex' => '#0284C7'],
            ['title' => 'Keep track of appointments', 'description' => 'Attend recommended visits and discuss blood pressure, test results, and any new symptoms.', 'icon' => 'calendar-check', 'color_hex' => '#16A34A'],
            ['title' => 'Prepare for third-trimester care', 'description' => 'Ask about upcoming checks, fetal movement guidance, and when to contact your maternity team.', 'icon' => 'clipboard-check', 'color_hex' => '#2563EB'],
            ['title' => 'Plan for birth and support', 'description' => 'Discuss birth preferences, support people, and your maternity unit’s admission process with your care team.', 'icon' => 'heart-handshake', 'color_hex' => '#7C3AED'],
            ['title' => 'Know when to seek urgent care', 'description' => 'Contact your maternity team promptly for reduced fetal movement, bleeding, fluid leakage, or other concerning symptoms.', 'icon' => 'alert-circle', 'color_hex' => '#DC2626'],
            ['title' => 'Review your birth plan', 'description' => 'Discuss pain relief, birth preferences, and circumstances that could change your care plan.', 'icon' => 'clipboard-list', 'color_hex' => '#2563EB'],
            ['title' => 'Prepare practical essentials', 'description' => 'Consider transport, support contacts, and what your maternity unit recommends bringing for birth.', 'icon' => 'briefcase-medical', 'color_hex' => '#16A34A'],
            ['title' => 'Discuss signs of labour', 'description' => 'Ask your maternity team how to recognize labour and when you should call or go to your birth facility.', 'icon' => 'phone', 'color_hex' => '#2563EB'],
            ['title' => 'Keep your contact plan ready', 'description' => 'Make sure you know how to reach maternity care at any hour and have transport arrangements if needed.', 'icon' => 'phone-call', 'color_hex' => '#0D9488'],
            ['title' => 'Review your birth facility plan', 'description' => 'Confirm where to go, who to contact, and what to do if labour begins or you have a concern.', 'icon' => 'hospital', 'color_hex' => '#2563EB'],
            ['title' => 'Ask about group B strep testing', 'description' => 'Ask your clinician whether group B streptococcus screening is recommended under your local guidelines.', 'icon' => 'stethoscope', 'color_hex' => '#7C3AED'],
            ['title' => 'Review the signs that need assessment', 'description' => 'Do not wait for a routine appointment if fetal movement decreases or you have bleeding, fluid leakage, or severe symptoms.', 'icon' => 'alert-circle', 'color_hex' => '#DC2626'],
            ['title' => 'Discuss your due date', 'description' => 'Ask your clinician what follow-up is recommended if you reach your estimated due date without going into labour.', 'icon' => 'calendar-days', 'color_hex' => '#16A34A'],
            ['title' => 'Keep your care team informed', 'description' => 'Contact your maternity team with questions about contractions, fluid leakage, bleeding, or changes in fetal movement.', 'icon' => 'phone', 'color_hex' => '#2563EB'],
            ['title' => 'Prepare for newborn care', 'description' => 'Ask about feeding support, newborn checks, and who to contact after discharge.', 'icon' => 'baby', 'color_hex' => '#DB2777'],
            ['title' => 'Follow your maternity team’s plan', 'description' => 'Your due date is an estimate. Follow your clinician’s advice about monitoring, labour, and any next steps.', 'icon' => 'heart-pulse', 'color_hex' => '#16A34A'],
        ];
    }
}
