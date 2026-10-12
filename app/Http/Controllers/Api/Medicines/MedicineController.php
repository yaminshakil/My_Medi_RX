<?php

namespace App\Http\Controllers\Api\Medicines;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Eprescription\CreateMedicineRequest;
use App\Http\Requests\Eprescription\UpdateMedicineRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\MedicineService;
use App\Services\ApiResponseService;
use App\Http\Resources\Medicines\MedicineResource;

class MedicineController extends Controller
{
    protected MedicineService $service;

    public function __construct(MedicineService $service)
    {
        $this->service = $service;
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

        $medicines = $this->service->list($perPage, $search);
        $medicineData = MedicineResource::collection($medicines);

        return ApiResponseService::success($medicineData, 'Manufacturer created successfully!');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMedicineRequest $request)
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
    public function update(UpdateMedicineRequest $request, string $id)
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
