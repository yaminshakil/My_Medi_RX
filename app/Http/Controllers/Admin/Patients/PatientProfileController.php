<?php

namespace App\Http\Controllers\Admin\Patients;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\CreatePatientProfileRequest;
use App\Http\Requests\Patients\UpdatePatientProfileRequest;
use App\Interfaces\Patients\PatientProfileRepositoryInterface;
use Inertia\Inertia;

class PatientProfileController extends Controller
{
    protected $patients;

    public function __construct(
        PatientProfileRepositoryInterface $patients,
    ) {
        $this->patients = $patients;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePatientProfileRequest $request)
    {
        $data = $request->validated();

        $patient = $this->patients->create($data);

        return redirect()->back()
            ->with('success', 'Profile Created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $patient = $this->patients->find($id);

        return Inertia::render('Profile/Patient/Edit', [
            'patient' => $patient,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientProfileRequest $request, $id)
    {
        $data = $request->validated();
        $this->patients->update($id, $data);

        return redirect()->back()
            ->with('success', 'Profile Updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->patients->delete($id);

        return redirect()->back()->with('success', 'Patient deleted successfully');
    }
}
