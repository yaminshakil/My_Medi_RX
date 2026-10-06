<?php

namespace App\Http\Controllers\Admin\Hospital;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Interfaces\HospitalRepositoryInterface;
use App\Models\Setting;

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
        return Inertia::render('Admin/Hospital/Main', [
            'hospital' => $hospital,
            'status' => session('status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Hospital/Create', [
            'status' => session('status'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);
        $this->hospitalRepository->createHospital($request->all());
        return redirect()->intended('/admin/hospitals')->with('success', 'Your Hospital has been created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $hospital = $this->hospitalRepository->getHospitalById($id);

        return Inertia::render('Admin/Hospital/Edit', [
                'hospital' => $hospital,
                'status' => session('status'),
            ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'hospital_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);
        $this->hospitalRepository->updateHospital($request->all(), $id);
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
