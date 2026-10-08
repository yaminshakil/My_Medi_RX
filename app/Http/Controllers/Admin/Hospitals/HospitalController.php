<?php

namespace App\Http\Controllers\Admin\Hospitals;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Interfaces\HospitalRepositoryInterface;
use App\Models\Setting;
use App\Http\Requests\Hospital\HospitalStoreRequest;
use App\Models\HospitalType;

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
        $hospital = $this->hospitalRepository->getAllHospital($perPage, $search);
        return Inertia::render('Admin/Hospitals/Main', [
            'hospital' => $hospital,
            'status' => session('status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $hospitalTypes = HospitalType::where('status', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);
        return Inertia::render('Admin/Hospitals/Create', [
            'hospitalTypes' => $hospitalTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HospitalStoreRequest $request)
    {
        $data = $request->validated();
        $this->hospitalRepository->createHospital($data);
        return redirect()->intended('/admin/hospitals')->with('success', 'Your Hospital has been created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $hospital = $this->hospitalRepository->getHospitalById($id);

        return Inertia::render('Admin/Hospitals/Show', [
            'hospital' => $hospital,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $hospital = $this->hospitalRepository->getHospitalById($id);
        $hospitalTypes = HospitalType::where('status', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Hospitals/Edit', [
                'hospital' => $hospital,
                'hospitalTypes' => $hospitalTypes,
                'status' => session('status'),
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HospitalStoreRequest $request, string $id)
    {
        $this->hospitalRepository->updateHospital($request->validated(), $id);
        return redirect()->intended('/admin/hospitals')->with('success', 'Your Hospital has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->hospitalRepository->deleteHospital($id);
        return redirect()->back()->with('success', 'Your category has been deleted successfully!');
    }
}
