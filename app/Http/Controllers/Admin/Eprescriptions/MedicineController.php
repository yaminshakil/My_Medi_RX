<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateMedicineRequest;
use App\Http\Requests\Eprescription\UpdateMedicineRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\MedicineService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicineController extends Controller
{
    protected MedicineService $service;

    public function __construct(MedicineService $service)
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

        $medicines = $this->service->list($perPage, $search);

        return Inertia::render('Admin/Eprescriptions/Medicines/Index', [
            'medicines' => $medicines,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        $manufacturers = $this->service->getManufacturer();

        $manufacturers = $manufacturers->map(function ($manufacturer) {
            return [
                'label' => $manufacturer->company_name,
                'value' => $manufacturer->id,
            ];
        });

        return Inertia::render('Admin/Eprescriptions/Medicines/Create', [
            'manufacturers' => $manufacturers,
        ]);
    }

    public function store(CreateMedicineRequest $request)
    {
        $data = $request->validated();

        $this->service->store($data);

        return redirect()->route('medicines.index')->with('success', 'Medicine created successfully.');
    }

    public function edit($id)
    {
        $manufacturers = $this->service->getManufacturer();

        $manufacturers = $manufacturers->map(function ($manufacturer) {
            return [
                'label' => $manufacturer->company_name,
                'value' => $manufacturer->id,
            ];
        });

        $medicine = $this->service->get($id);

        return Inertia::render('Admin/Eprescriptions/Medicines/Edit', [
            'medicine' => $medicine,
            'manufacturers' => $manufacturers,
        ]);
    }

    public function update(UpdateMedicineRequest $request, $id)
    {
        $data = $request->validated();

        $this->service->update($id, $data);

        return redirect()->route('medicines.index')->with('success', 'Medicine updated successfully.');
    }

    public function destroy($id)
    {
        $this->service->destroy($id);

        return redirect()->route('medicines.index')->with('success', 'Medicine deleted successfully.');
    }
}
