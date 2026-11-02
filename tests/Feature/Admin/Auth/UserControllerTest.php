<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\Doctor;
use App\Models\MedicationLog;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PatientMedication;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_show_renders_prescription_and_adherence_analytics(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 15)->setTime(12, 0));
        $admin = $this->adminWithUserPermission();
        $doctor = $this->doctor();
        $firstPatient = $this->patient('MRX-1001', 'Amina Rahman');
        $secondPatient = $this->patient('MRX-1002', 'Nadia Akter');

        $newVisit = $this->prescription($doctor, $firstPatient, 'RX-1001', now(), false);
        $otherDoctor = $this->user();
        $this->prescription($otherDoctor, $firstPatient, 'RX-OTHER', now(), false);
        $this->prescription($doctor, $firstPatient, 'RX-1002', now(), true);
        $this->prescription($doctor, $secondPatient, 'RX-1003', now()->subDays(3), false);
        $this->prescription($doctor, $secondPatient, 'RX-1004', now(), false, 'draft');
        $this->prescription($doctor, $secondPatient, 'RX-1005', now()->subMonth(), false);

        $medicine = Medicine::query()->create([
            'doctor_id'    => $doctor->id,
            'generic_name' => 'Amoxicillin',
            'brand_name'   => 'Amoxi',
            'type'         => 'Capsule',
        ]);
        $prescriptionMedicine = PrescriptionMedicine::query()->create([
            'prescription_id' => $newVisit->id,
            'medicine_id'     => $medicine->id,
            'type'            => 'Capsule',
            'strength'        => '500 mg',
            'dosage'          => 'Three times daily',
            'duration'        => '7 days',
        ]);
        $patientMedication = PatientMedication::query()->create([
            'patient_id'               => $firstPatient->id,
            'prescription_medicine_id' => $prescriptionMedicine->id,
            'medicine_name'            => 'Amoxicillin',
            'start_date'               => now()->subDays(5)->toDateString(),
            'source'                   => 'doctor_prescription',
        ]);

        $this->createMedicationLog($firstPatient, $patientMedication, 'taken', now()->subDay());
        $this->createMedicationLog($firstPatient, $patientMedication, 'taken', now()->subDays(2));
        $this->createMedicationLog($firstPatient, $patientMedication, 'missed', now()->subDays(3));
        $this->createMedicationLog($firstPatient, $patientMedication, 'skipped', now()->subDays(4));

        $this->assertDatabaseHas('prescriptions', [
            'prescription_number' => 'RX-1004',
            'status'              => 'draft',
        ]);

        $this->actingAs($admin)
            ->get(route('users.show', $doctor->id))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Admin/Users/show')
                ->where('user.doctor.specialization', 'Family Medicine')
                ->where('doctorAnalytics.daily.patients', 1)
                ->where('doctorAnalytics.daily.prescriptions', 2)
                ->where('doctorAnalytics.daily.new_visits', 1)
                ->where('doctorAnalytics.daily.follow_up_visits', 1)
                ->where('doctorAnalytics.monthly.patients', 2)
                ->where('doctorAnalytics.monthly.prescriptions', 3)
                ->where('doctorAnalytics.adherence.eligible_doses', 3)
                ->where('doctorAnalytics.adherence.taken', 2)
                ->where('doctorAnalytics.adherence.missed', 1)
                ->where('doctorAnalytics.adherence.skipped', 1)
                ->where('doctorAnalytics.adherence.percentage', 66.67)
                ->where('doctorAnalytics.patient_adherence.0.patient_name', 'Amina Rahman'));
    }

    public function test_doctor_analytics_csv_export_includes_summary_and_prescription_log(): void
    {
        $admin = $this->adminWithUserPermission();
        $doctor = $this->doctor();
        $patient = $this->patient('MRX-2001', '=SUM(A1:A2)');
        $prescription = $this->prescription($doctor, $patient, 'RX-2001', now(), false, 'issued', '=HYPERLINK("https://example.test","open")');
        $medicine = Medicine::query()->create([
            'doctor_id'    => $doctor->id,
            'generic_name' => 'Amoxicillin',
            'brand_name'   => 'Amoxi',
            'type'         => 'Capsule',
        ]);
        PrescriptionMedicine::query()->create([
            'prescription_id' => $prescription->id,
            'medicine_id'     => $medicine->id,
            'type'            => 'Capsule',
            'strength'        => '500 mg',
            'dosage'          => 'Three times daily',
            'duration'        => '7 days',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('users.analytics.export', ['id' => $doctor->id, 'format' => 'csv']));

        $response->assertOk()
            ->assertDownload('doctor-'.$doctor->id.'-clinical-report.csv');

        $this->assertStringContainsString('Monthly analytics', $response->streamedContent());
        $this->assertStringContainsString("'=SUM(A1:A2)", $response->streamedContent());
        $this->assertStringContainsString("'=HYPERLINK", $response->streamedContent());
        $this->assertStringContainsString('Amoxi 500 mg Three times daily', $response->streamedContent());
    }

    public function test_doctor_analytics_pdf_export_downloads_clinical_report(): void
    {
        $admin = $this->adminWithUserPermission();
        $doctor = $this->doctor();

        $this->actingAs($admin)
            ->get(route('users.analytics.export', ['id' => $doctor->id, 'format' => 'pdf']))
            ->assertOk()
            ->assertDownload('doctor-'.$doctor->id.'-clinical-report.pdf')
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_non_doctor_cannot_export_doctor_analytics(): void
    {
        $admin = $this->adminWithUserPermission();
        $nonDoctor = $this->user();

        $this->actingAs($admin)
            ->get(route('users.analytics.export', ['id' => $nonDoctor->id, 'format' => 'csv']))
            ->assertNotFound();
    }

    private function adminWithUserPermission(): User
    {
        $permission = Permission::firstOrCreate([
            'name'       => 'users.menu',
            'guard_name' => 'web',
        ]);
        $role = Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permission);

        $user = $this->user();
        $user->assignRole($role);

        return $user;
    }

    private function doctor(): User
    {
        $role = Role::firstOrCreate([
            'name'       => 'Doctor',
            'guard_name' => 'web',
        ]);
        $doctor = $this->user();
        $doctor->assignRole($role);

        Doctor::query()->create([
            'user_id'         => $doctor->id,
            'specialization'  => 'Family Medicine',
            'registration_no' => 'DOC-'.$doctor->id,
        ]);

        return $doctor;
    }

    private function user(): User
    {
        return User::factory()->create([
            'first_name' => 'Doctor',
            'last_name'  => 'Test',
        ]);
    }

    private function patient(string $patientNumber, string $name): Patient
    {
        return Patient::query()->create([
            'patient_number' => $patientNumber,
            'name'           => $name,
            'phone'          => '01700000000',
        ]);
    }

    private function prescription(
        User $doctor,
        Patient $patient,
        string $prescriptionNumber,
        \DateTimeInterface $createdAt,
        bool $isFollowup,
        string $status = 'issued',
        ?string $diagnosis = 'Routine check-up',
    ): Prescription {
        $prescription = Prescription::query()->create([
            'patient_id'          => $patient->id,
            'doctor_id'           => $doctor->id,
            'prescription_number' => $prescriptionNumber,
            'diagnosis'           => $diagnosis,
            'is_followup'         => $isFollowup,
            'status'              => $status,
            'followup_date'       => now()->addDays(7)->toDateString(),
            'header_left'         => '',
            'header_right'        => '',
            'footer_info'         => '',
            'created_at'          => $createdAt,
            'updated_at'          => $createdAt,
        ]);
        $prescription->timestamps = false;
        $prescription->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $prescription;
    }

    private function createMedicationLog(
        Patient $patient,
        PatientMedication $medication,
        string $status,
        \DateTimeInterface $scheduledAt,
    ): MedicationLog {
        return MedicationLog::query()->create([
            'patient_id'            => $patient->id,
            'patient_medication_id' => $medication->id,
            'scheduled_date'        => $scheduledAt->format('Y-m-d'),
            'scheduled_at'          => $scheduledAt,
            'status'                => $status,
        ]);
    }
}
