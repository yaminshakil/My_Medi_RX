<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateManufacturerRequest;
use App\Http\Requests\Eprescription\UpdateManufacturerRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\ManufacturerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManufacturerController extends Controller
{
    protected ManufacturerService $service;

    public function __construct(ManufacturerService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);
        $search = null;

        if ($request->filled('search')) {
            $search = $request->search;
        }

        $perPage = $request->input('per_page', $recordsPerPage); // default 10

        $manufacturers = $this->service->getAll($perPage, $search);

        return Inertia::render('Admin/Eprescriptions/Manufacturers/Index', [
            'manufacturers' => $manufacturers,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function store(CreateManufacturerRequest $request)
    {
        $validated = $request->validated();

        $this->service->create($validated);

        return redirect()->back()->with('success', 'Manufacturer created successfully');
    }

    public function update(UpdateManufacturerRequest $request, $id)
    {
        $validated = $request->validated();

        $this->service->update($id, $validated);

        return redirect()->back()->with('success', 'Manufacturer updated successfully');
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()->back()->with('success', 'Manufacturer deleted successfully');
    }
}
