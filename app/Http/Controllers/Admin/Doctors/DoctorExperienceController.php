<?php

namespace App\Http\Controllers\Admin\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateExperienceRequest;
use App\Http\Requests\Doctor\UpdateExperienceRequest;
use App\Services\Doctors\DoctorExperienceService;
use Illuminate\Support\Facades\Auth;

class DoctorExperienceController extends Controller
{
    protected $experienceService;

    public function __construct(DoctorExperienceService $experienceService)
    {
        $this->experienceService = $experienceService;
    }

    public function index()
    {
        $doctorId = Auth::user()->doctor->id; // adjust if needed
        $experiences = $this->experienceService->listByDoctor($doctorId);

        return inertia('Profile/Doctor/Experience/Index', [
            'experiences' => $experiences,
        ]);
    }

    public function store(CreateExperienceRequest $request)
    {
        $data = $request->validated();

        $doctorId = Auth::user()->doctor->id;

        $this->experienceService->createForDoctor($doctorId, $data);

        return redirect()->back()->with('success', 'Experience added successfully.');
    }

    public function update(UpdateExperienceRequest $request, $id)
    {
        $data = $request->validated();

        $this->experienceService->update($id, $data);

        return redirect()->back()->with('success', 'Experience updated successfully.');
    }

    public function destroy($id)
    {
        $this->experienceService->delete($id);

        return redirect()->back()->with('success', 'Experience deleted successfully.');
    }
}
