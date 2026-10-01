<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateMedicineDosesRequest;
use App\Http\Requests\Eprescription\UpdateMedicineDosesRequest;
use App\Models\Setting;
use App\Services\Eprescriptions\MedicineDoseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicineDoseController extends Controller
{
    protected MedicineDoseService $service;

    public function __construct(MedicineDoseService $service)
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

        $doses = $this->service->getAll($perPage, $search);

        return Inertia::render('Admin/Eprescriptions/MedicineDoses/Index', [
            'doses' => $doses,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    public function store(CreateMedicineDosesRequest $request)
    {
        $data = $request->validated();

        $data['created_by'] = auth()->id();
        $this->service->create($data);

        return redirect()->back()->with('success', 'Medicine dose created successfully.');
    }

    public function update(UpdateMedicineDosesRequest $request, int $id)
    {
        $data = $request->validated();

        $data['updated_by'] = auth()->id();
        $this->service->update($id, $data);

        return redirect()->back()->with('success', 'Medicine dose updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return redirect()->back()->with('success', 'Medicine dose deleted successfully.');
    }
}
