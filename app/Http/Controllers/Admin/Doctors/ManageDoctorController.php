<?php

namespace App\Http\Controllers\Admin\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateDoctorRequest;
use App\Http\Requests\Doctor\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Models\Setting;
use App\Models\User;
use App\Services\Doctors\DoctorService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManageDoctorController extends Controller
{
    public function __construct(
        protected DoctorService $doctorService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recordsPerPage = Setting::getValue('records_per_page', 10);

        $doctors = User::query();

        $doctors->with('doctor');

        if ($request->filled('search')) {
            $search = $request->search;
            $doctors->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            );
        }

        $perPage = $request->input('per_page', $recordsPerPage); // default 10

        $doctors = $doctors->role('Doctor')
            ->latest()->paginate($perPage)->withQueryString();

        $doctors->getCollection()->transform(fn ($user) => [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'featured'   => $user?->doctor?->featured,
            'active'     => $user?->doctor?->active,
            'doctor'     => $user->doctor,
            'created_at' => $user->created_at->format('d M Y'),
        ]);

        return Inertia::render('Admin/Doctors/Index', [
            'doctors' => $doctors,
            'filters' => $request->only(['search', 'per_page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialties = $this->doctorService->getSpecialtiesForSelect();

        return Inertia::render('Admin/Doctors/Create', [
            'specialties' => $specialties,
        ]);
    }

    public function show(Doctor $doctor): Response
    {
        $doctor->load([
            'user:id,name,email,mobile,created_at',
            'user.profileImage',
            'user.specialties:id,name',
            'educations'  => fn (HasMany $query) => $query->orderByDesc('year')->orderByDesc('id'),
            'experiences' => fn (HasMany $query) => $query->orderByDesc('from_year')->orderByDesc('id'),
        ]);
        $doctor->user->setAppends(['profile_image_url']);

        foreach ($doctor->user->specialties as $specialty) {
            $specialty->setAppends([]);
        }

        return Inertia::render('Admin/Doctors/Show', [
            'doctor' => $doctor,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateDoctorRequest $request)
    {
        $data = $request->validated();

        $this->doctorService->create($data);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        $doctor->load('user', 'user.specialties', 'user.profileImage');

        $specialties = $this->doctorService->getSpecialtiesForSelect();

        return Inertia::render('Admin/Doctors/Edit', [
            'doctor'      => $doctor,
            'specialties' => $specialties,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $data = $request->validated();
        $this->doctorService->update($data, $doctor);

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $this->doctorService->delete($doctor);

        return redirect()->back()->with('success', 'Doctor Deleted successfully.');
    }

    public function toggleFeatured(Doctor $doctor)
    {
        $doctor->update(['featured' => ! $doctor->featured]);

        return redirect()->back()->with('success', 'Doctor updated successfully.');
    }

    public function toggleActive(Doctor $doctor)
    {
        $user = $doctor->user;
        if ($user->status === 'suspended') {
            return back()->with('error', 'Suspended users cannot be toggled. Change their status from the suspension screen.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);
        $doctor->update(['active' => ! $doctor->active]);

        return redirect()->back()->with('success', 'Doctor updated successfully.');
    }
}
