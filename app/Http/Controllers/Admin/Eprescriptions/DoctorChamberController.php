<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\CreateDoctorChamberRequest;
use App\Http\Requests\Eprescription\UpdateDoctorChamberRequest;
use App\Interfaces\Eprescriptions\DoctorChamberRepositoryInterface;
use App\Models\DoctorChamber;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DoctorChamberController extends Controller
{
    private $doctorChamberRepository;

    protected $user;

    protected $doctor_id;

    public function __construct(DoctorChamberRepositoryInterface $doctorChamberRepository)
    {
        $this->doctorChamberRepository = $doctorChamberRepository;
        $this->user = Auth::user();

        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = $this->user->doctor_assistant->doctor_id;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $doctorChambers = DoctorChamber::query();
        $perPage = $request->input('per_page', 10); // default 10

        if ($request->filled('search')) {
            $search = $request->search;
            $doctorChambers->where('name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        }
        // If user is Doctor
        if ($this->doctor_id) {
            $doctorChambers = $doctorChambers->where('doctor_id', $this->doctor_id);
        }

        $doctorChambers = $doctorChambers->latest()->paginate($perPage)->withQueryString();
        $doctorChambers->getCollection()->transform(fn ($doctorChamber) => [
            'id' => $doctorChamber->id,
            'name' => $doctorChamber->name,
            'header_left' => $doctorChamber->header_left,
            'header_right' => $doctorChamber->header_right,
            'footer_info' => $doctorChamber->footer_info,
            'is_active' => $doctorChamber->is_active,
        ]);

        return Inertia::render('Admin/Eprescriptions/Chambers/Index', [
            'doctorChambers' => $doctorChambers,
            'filters' => $request->only(['search', 'per_page']),
            'status' => session('status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $hospitals = Hospital::all();
        $hospitaloptions = $hospitals->map(function ($hospital) {
            return [
                'value' => $hospital->id,
                'label' => $hospital->hospital_name
            ];
        });
        return Inertia::render('Admin/Eprescriptions/Chambers/Create', [
            'hospitaloptions' => $hospitaloptions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateDoctorChamberRequest $request)
    {
        $data = $request->validated();
        $this->doctorChamberRepository->create($data);

        return redirect()->route('chambers.index')->with('success', 'Your chamber has been created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $doctorChamber = $this->doctorChamberRepository->find($id);

        $hospitals = Hospital::all();
        $hospitaloptions = $hospitals->map(function ($hospital) {
            return [
                'value' => $hospital->id,
                'label' => $hospital->hospital_name
            ];
        });

        return Inertia::render('Admin/Eprescriptions/Chambers/Edit', [
            'doctorChamber' => [
                'id' => $doctorChamber->id,
                'name' => $doctorChamber->name,
                'header_left' => $doctorChamber->header_left,
                'header_right' => $doctorChamber->header_right,
                'footer_info' => $doctorChamber->footer_info,
                'schedules' => $doctorChamber->schedules,
                'fee' => $doctorChamber->fee,
                'followup_fee' => $doctorChamber->followup_fee,
                'report_fee' => $doctorChamber->report_fee,
                'chamber_logo' => $doctorChamber->chamber_logo,
                'chamber_logo_url' => $doctorChamber->chamber_logo_url,
                'city' => $doctorChamber->city,
                'address' => $doctorChamber->address,
                'appoinment_limit' => $doctorChamber->appoinment_limit,
                'hospital_id' => $doctorChamber->hospital_id,
            ],
            'hospitaloptions' => $hospitaloptions
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorChamberRequest $request, string $id)
    {
        $data = $request->validated();
        $doctorChamber = $this->doctorChamberRepository->update($data, $id);

        return redirect()->route('chambers.index')->with('success', 'Your chamber has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->doctorChamberRepository->delete($id);
    }

    public function switchChamber($chamber_id)
    {
        $this->doctorChamberRepository->switchChamber($chamber_id);

        return redirect()->back()->with('success', 'Your chamber has been updated successfully!');
    }
}
