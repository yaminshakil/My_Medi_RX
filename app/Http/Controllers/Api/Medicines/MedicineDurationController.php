<?php

namespace App\Http\Controllers\Api\Medicines;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Eprescription\CreateMedicineDurationRequest;
use App\Http\Requests\Eprescription\UpdateMedicineDurationRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\MedicineDurationService;
use App\Services\ApiResponseService;
use App\Http\Resources\Medicines\MedicineDurationResource;

class MedicineDurationController extends Controller
{
    protected $service;

    public function __construct(MedicineDurationService $service)
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

        $durations = $this->service->paginate($search, $perPage);
        $durations = MedicineDurationResource::collection($durations);
        $durationdata = [
             'durations' => $durations,
             'filters' => $request->only(['search', 'per_page']),
         ];
        return ApiResponseService::success($durationdata, 'Medicine dose updated successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMedicineDurationRequest $request)
    {
        $data = $request->validated();

        $data['created_by'] = auth()->id();
        $duration = $this->service->store($data);
        $duration = new MedicineDurationResource($duration);
        return ApiResponseService::success($duration, 'Medicine dose created successfully.');
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
    public function update(UpdateMedicineDurationRequest $request, string $id)
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();
        $this->service->update($id, $data);
        $duration = $this->service->find($id);
        $duration = new MedicineDurationResource($duration);
        return ApiResponseService::success($duration, 'Duration updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->delete($id);
        return ApiResponseService::success([], 'Medicine duration deleted successfully.');
    }
}
