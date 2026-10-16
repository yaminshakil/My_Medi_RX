<?php

namespace App\Http\Controllers\Api\Medicines;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateMedicineRequest;
use App\Http\Requests\Eprescription\UpdateMedicineRequest;
use App\Http\Resources\Medicines\MedicineResource;
use App\Models\Setting;
use App\Services\ApiResponseService;
use App\Services\Eprescriptions\MedicineService;
use Illuminate\Http\Request;

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

        return ApiResponseService::success($medicineData, 'Manufacturer retrive successfully!');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMedicineRequest $request)
    {
        $data = $request->validated();
        $medicine = $this->service->store($data);
        $medicineData = new MedicineResource($medicine);

        return ApiResponseService::success($medicineData, 'Medicine created successfully!');
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
        $data = $request->validated();

        $this->service->update($id, $data);
        $medicine = $this->service->get($id);
        $medicine = $this->service->get($id);
        $medicineData = new MedicineResource($medicine);

        return ApiResponseService::success($medicineData, 'Medicine created successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->destroy($id);

        return ApiResponseService::success([], 'Medicine deleted successfully!');
    }
}
