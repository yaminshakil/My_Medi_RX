<?php

namespace App\Http\Controllers\Frontend\Diagnostic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
use Inertia\Inertia;
use App\Models\DoctorChamber;
use App\Models\User;
use App\Models\DiagnosticService;
use App\Models\DiagnosticRating;
use Illuminate\Support\Str;

class DiagnosticController extends Controller
{
    public function index()
    {
        $userLat = request('latitude');
        $userLng = request('longitude');
        $division_id = request('division_id');
        $district_id = request('district_id');
        

        $query = Hospital::withCount('doctors');

        // If user location is available, calculate distance
        if ($userLat && $userLng) {
            $query->selectRaw(
                "hospitals.*, 
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians(?)) 
                    + sin(radians(?)) * sin(radians(latitude))
                )) AS distance",
                [$userLat, $userLng, $userLat]
            )
            ->orderBy('distance', 'asc');
        }

        if($division_id) {
            $query->where('division_id', $division_id);
        }

        if($district_id) {
            $query->where('district_id', $district_id);
        }

        $hospitals = $query->paginate(12);

        // If it's an AJAX/axios request, return JSON only
        if (request()->wantsJson()) {
            return response()->json($hospitals);
        }

        // Otherwise return Inertia page (first load)
        return Inertia::render('Frontend/Hospitals/Index', [
            'hospitals' => $hospitals,
        ]);
    }

    public function show(Hospital $hospital)
    {
        //$hospital = Hospital::findOrFail($id);
        $hospital->load([
            'doctors' => fn ($q) => $q->with(['profile', 'specialties', 'specialties.parent', 'chambers']),
            'services',
            'ratings.user',
            'offers',
            'offers.offerable'
        ]);

        return Inertia::render('Frontend/Hospitals/Show', [
            'hospital' => $hospital,
            'averageRating' => round($hospital->ratings()->avg('stars'), 1),
            'seo' => [
            'title' => $hospital->hospital_name,
            'description' => Str::limit(strip_tags($hospital->hospital_description), 160),
            'image' => $hospital->banner_url
                ? asset('storage/'.$hospital->banner_url)
                : asset('images/default-banner.jpg'),
            'url' => route('diagnostics.show', $hospital->uuid),
        ],
        ]);
    }

    // Update Chamber Schedule

    public function getChamberSchedule($hospital_uuid, $doctor_uuid)
    {
        $doctor_id = User::where('uuid', $doctor_uuid)?->first()?->id;

        $hospital_id = Hospital::where('uuid', $hospital_uuid)?->first()?->id;

        $doctorChamber = DoctorChamber::with('doctor')->where('hospital_id', $hospital_id)->where('user_id', $doctor_id)->first();
        return Inertia::render('Frontend/Hospitals/Chambers/Edit', [
            'doctorChamber' => $doctorChamber,
        ]);

    }

    public function updateChamberSchedule(Request $request, $id)
    {
        $doctorChamber = DoctorChamber::findOrFail($id);
        $doctorchamber = $doctorChamber->update([
            'schedules'   => $request->schedules
        ]);

    }

    public function addDiagnosticServices(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|between:0,99.99',
            'hospital_id' => 'required|numeric',
        ]);

        $DiagnosticService = DiagnosticService::create($data);

        return redirect()->back()->with('success', 'Service Created Successfully');
    }

    public function updateDiagnosticServices(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|between:0,99.99',
            'hospital_id' => 'required|numeric',
        ]);

        $DiagnosticService = DiagnosticService::findOrFail($id);

        $DiagnosticService = $DiagnosticService->update($data);

        return redirect()->back()->with('success', 'Service Updated Successfully');
    }

    public function deleteDiagnosticServices($id)
    {

        $DiagnosticService = DiagnosticService::findOrFail($id);

        $DiagnosticService = $DiagnosticService->delete();

        return redirect()->back()->with('success', 'Service Deleted Successfully');
    }

    public function storeRating(Request $request, $id)
    {
        $validated = $request->validate([
            'stars'   => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        DiagnosticRating::updateOrCreate(
            [
                'hospital_id' => $id,
                'user_id'     => auth()->id(),
            ],
            $validated
        );

        return redirect()->back()->with('success', 'Rating submitted!');
    }
}
