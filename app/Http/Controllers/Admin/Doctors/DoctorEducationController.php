<?php

namespace App\Http\Controllers\Admin\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\CreateEducationRequest;
use App\Http\Requests\Doctor\UpdateEducationRequest;
use App\Services\Doctors\DoctorEducationService;
use Illuminate\Support\Facades\Auth;

class DoctorEducationController extends Controller
{
    protected $educationService;

    public function __construct(DoctorEducationService $educationService)
    {
        $this->educationService = $educationService;
    }

    public function index()
    {
        $doctorId = Auth::user()->doctor->id; // Adjust based on your auth structure
        $educations = $this->educationService->listByDoctor($doctorId);

        return inertia('Profile/Doctor/Education/Index', [
            'educations' => $educations,
        ]);
    }

    public function store(CreateEducationRequest $request)
    {
        $request->validated();

        $doctorId = Auth::user()->doctor->id;

        $this->educationService->createForDoctor($doctorId, $request->only([
            'degree', 'institute', 'year', 'country',
        ]));

        return redirect()->back()->with('success', 'Education added successfully.');
    }

    public function update(UpdateEducationRequest $request, $id)
    {
        $request->validated();

        $this->educationService->update($id, $request->only([
            'degree', 'institute', 'year', 'country',
        ]));

        return redirect()->back()->with('success', 'Education updated successfully.');
    }

    public function destroy($id)
    {
        $this->educationService->delete($id);

        return redirect()->back()->with('success', 'Education deleted successfully.');
    }
}
