<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateMedicineDurationRequest;
use App\Http\Requests\Eprescription\UpdateMedicineDurationRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\MedicineDurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicineDurationController extends Controller
{
    protected $service;

    public function __construct(MedicineDurationService $service)
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

        $durations = $this->service->paginate($search, $perPage);

        return Inertia::render('Admin/Eprescriptions/MedicineDurations/Index', [
            'durations' => $durations,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function store(CreateMedicineDurationRequest $request)
    {
        $data = $request->validated();

        $data['created_by'] = auth()->id();
        $this->service->store($data);

        return redirect()->back()->with('success', 'Duration created successfully');
    }

    public function update(UpdateMedicineDurationRequest $request, $id)
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();
        $this->service->update($id, $data);

        return redirect()->back()->with('success', 'Duration updated successfully');
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return redirect()->back()->with('success', 'Duration deleted successfully');
    }
}
