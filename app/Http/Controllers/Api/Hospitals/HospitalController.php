<?php

namespace App\Http\Controllers\api\Hospitals;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\HospitalRepositoryInterface;
use App\Models\Setting;
use App\Services\ApiResponseService;
use App\Http\Requests\Hospital\HospitalStoreRequest;

class HospitalController extends Controller
{
    private $hospitalRepository;

    public function __construct(HospitalRepositoryInterface $hospitalRepository)
    {
        $this->hospitalRepository = $hospitalRepository;
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
        $hospitals = $this->hospitalRepository->getAllHospital($perPage, $search);
        return ApiResponseService::success($hospitals, 'Hospitals retrieved successfully!');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HospitalStoreRequest $request)
    {
        $data = $request->validated();
        $hospital = $this->hospitalRepository->createHospital($data);
        return ApiResponseService::success($hospital, 'Hospitals retrieved successfully!');
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

    /**
     * Display a listing of the resource.
     */
    public function getAllHospitalType(Request $request)
    {
        $hospitalTypes = $this->hospitalRepository->getAllHospitalType();
        return ApiResponseService::success($hospitalTypes, 'Hospital types retrieved successfully!');
    }
}
