<?php

namespace App\Http\Controllers\Api\Medicines;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Eprescription\CreateMedicineDosesRequest;
use App\Http\Requests\Eprescription\UpdateMedicineDosesRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\MedicineDoseService;
use App\Services\ApiResponseService;
use App\Http\Resources\Medicines\MedicineDoseResource;

class MedicineDoseController extends Controller
{
    protected MedicineDoseService $service;

    public function __construct(MedicineDoseService $service)
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

        $doses = $this->service->getAll($perPage, $search);
        $doses = MedicineDoseResource::collection($doses);

        $dosesdata = [
             'doses' => $doses,
             'filters' => $request->only(['search', 'per_page']),
         ];

        return ApiResponseService::success($dosesdata, 'Medicine dose updated successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMedicineDosesRequest $request)
    {
        $data = $request->validated();

        $data['created_by'] = auth()->id();
        $doses = $this->service->create($data);
        $doses = new MedicineDoseResource($doses);
        return ApiResponseService::success($doses, 'Medicine dose created successfully.');
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
    public function update(UpdateMedicineDosesRequest $request, string $id)
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();
        $this->service->update($id, $data);
        $doses = $this->service->getById($id);
        $doses = new MedicineDoseResource($doses);
        return ApiResponseService::success($doses, 'Medicine dose created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->delete($id);
        return ApiResponseService::success([], 'Medicine dose deleted successfully.');
    }
}
