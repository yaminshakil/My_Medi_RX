<?php

namespace App\Http\Controllers\Api\Doctors;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MedicalSpecialty\MedicalSpecialtyService;
use App\Models\Setting;
use App\Http\Resources\Doctors\MedicalSpecialtyGroupResource;
use App\Services\Doctors\DoctorService;

class MedicalSpecialtyController extends Controller
{
    protected $service;
    protected $doctorService;

    public function __construct(MedicalSpecialtyService $service, DoctorService $doctorService)
    {
        $this->service = $service;
        $this->doctorService = $doctorService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);
        $search = null;
        if ($request->filled('search')) {
            $search = $request->search;
        }
        $perPage = $request->input('per_page', $recordsPerPage); // default 10
        $specialties = $this->service->getAll($search, $perPage);

        $specialties = $this->doctorService->getSpecialtiesForSelect();

        return MedicalSpecialtyGroupResource::collection($specialties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
