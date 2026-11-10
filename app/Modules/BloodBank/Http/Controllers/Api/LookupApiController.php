<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Modules\BloodBank\Models\Patient;
use App\Modules\BloodBank\Repositories\Contracts\PatientRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\PhysicianRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\StorageUnitRepositoryInterface;
use App\Modules\BloodBank\Support\LookupOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupApiController extends BloodBankApiController
{
    public function __construct(
        private readonly PatientRepositoryInterface $patients,
        private readonly PhysicianRepositoryInterface $physicians,
        private readonly StorageUnitRepositoryInterface $storageUnits,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success([
            'groups'     => LookupOptions::groups(),
            'components' => LookupOptions::components(),
            'statuses'   => LookupOptions::statuses(),
            'urgencies'  => LookupOptions::urgencies(),
            'physicians' => $this->physicians->active()->map(fn ($physician) => [
                'id'         => $physician->id,
                'name'       => $physician->name,
                'department' => $physician->department,
            ])->values(),
            'storage_units' => $this->storageUnits->active()->map(fn ($unit) => [
                'id'   => $unit->id,
                'name' => $unit->name,
                'type' => $unit->type,
            ])->values(),
        ], 'Blood-bank lookup options loaded.');
    }

    public function components(): JsonResponse
    {
        return $this->success(
            LookupOptions::components(),
            'Blood components loaded.',
        );
    }

    public function getPatients(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        return $this->success(
            $this->patients->search($data['search'], 20)->map(fn (Patient $patient) => [
                'id'               => $patient->id,
                'name'             => $patient->name,
                'mrn'              => $patient->mrn,
                'clinical_file_no' => $patient->clinical_file_no,
                'blood_group'      => $patient->blood_group?->value,
            ])->values(),
            'Patients loaded.',
        );
    }
}
