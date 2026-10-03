<?php

namespace App\Http\Controllers\Api\Medicines;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Eprescription\CreateManufacturerRequest;
use App\Http\Requests\Eprescription\UpdateManufacturerRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\ManufacturerService;

class ManufacturerController extends Controller
{
    protected ManufacturerService $service;

    public function __construct(ManufacturerService $service)
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

        $manufacturers = $this->service->getAll($perPage, $search);
        return response()->json($manufacturers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateManufacturerRequest $request)
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
    public function update(UpdateManufacturerRequest $request, string $id)
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
