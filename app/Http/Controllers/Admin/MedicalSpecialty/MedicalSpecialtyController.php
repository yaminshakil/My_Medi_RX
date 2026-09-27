<?php

namespace App\Http\Controllers\Admin\MedicalSpecialty;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalSpecialty\MedicalSpecialtyRequest;
use App\Models\MedicalSpecialty;
use App\Models\Setting;
use App\Services\MedicalSpecialty\MedicalSpecialtyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicalSpecialtyController extends Controller
{
    protected $service;

    public function __construct(MedicalSpecialtyService $service)
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
        $specialties = $this->service->getAll($search, $perPage);

        return Inertia::render('Admin/MedicalSpecialties/Index', [
            'specialties' => $specialties,
            'filters'     => $request->only(['search', 'per_page']),
        ]);
    }

    public function create()
    {
        $parents = MedicalSpecialty::where('parent_id', null)->pluck('name', 'id');

        return Inertia::render('Admin/MedicalSpecialties/Create', [
            'parents' => $parents,
        ]);
    }

    public function store(MedicalSpecialtyRequest $request)
    {
        $this->service->store($request->validated());

        return redirect()->route('medical-specialties.index')->with('success', 'Created successfully');
    }

    public function edit(MedicalSpecialty $medicalSpecialty)
    {
        $parents = MedicalSpecialty::where('parent_id', null)->pluck('name', 'id');

        return Inertia::render('Admin/MedicalSpecialties/Edit', [
            'specialty' => $medicalSpecialty,
            'parents'   => $parents,
        ]);
    }

    public function update(MedicalSpecialtyRequest $request, MedicalSpecialty $medicalSpecialty)
    {
        $this->service->update($medicalSpecialty, $request->validated());

        return redirect()->route('medical-specialties.index')->with('success', 'Updated successfully');
    }

    public function destroy(MedicalSpecialty $medicalSpecialty)
    {
        $this->service->destroy($medicalSpecialty);

        return redirect()->route('medical-specialties.index')->with('success', 'Deleted successfully');
    }
}
