<?php

namespace App\Http\Controllers\Api\V1\Prescriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Eprescriptions\PrescriptionService;
use App\Models\Setting;
use App\Services\ApiResponseService;

class PrescriptionController extends Controller
{
    protected $prescriptionService;

    public function __construct(PrescriptionService $prescriptionService)
    {
        $this->prescriptionService = $prescriptionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);
        $filters = $request->only(['search', 'perPage', 'sort_by', 'sort_direction']);
        $perPage = $request->input('perPage', $recordsPerPage);
        $prescriptions = $this->prescriptionService->all($filters, $perPage);

        return ApiResponseService::success(
            $prescriptions,
            'Prescriptions retrieved successfully!'
        );
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
        $prescription = $this->prescriptionService->find($id);
        return ApiResponseService::success(
            $prescription,
            'Prescription retrieved successfully!'
        );
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
