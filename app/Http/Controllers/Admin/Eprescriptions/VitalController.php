<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Eprescription\VitalCreateRequest;
use App\Interfaces\Eprescriptions\VitalRepositoryInterface;
use App\Models\Vital;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VitalController extends Controller
{
    protected $vitalRepository;

    public function __construct(VitalRepositoryInterface $vitalRepository)
    {
        $this->vitalRepository = $vitalRepository;
    }

    public function index(Request $request)
    {
        $vitals = Vital::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $vitals->whereHas('patient', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }
        $vitals = $vitals->with('patient')->latest()->paginate(10)->withQueryString();
        $vitals->getCollection()->transform(fn ($vital) => [
            'id' => $vital->id,
            'name' => $vital->patient?->name,
            'blood_pressure' => $vital->blood_pressure,
            'heart_rate' => $vital->heart_rate,
            'temperature' => $vital->temperature,
            'respiratory_rate' => $vital->respiratory_rate,
            'oxygen_saturation' => $vital->oxygen_saturation,
            'created_at' => $vital->created_at->format('d M Y'),
        ]);

        return Inertia::render('Admin/Eprescriptions/Vitals/Index', [
            'vitals' => $vitals,
        ]);
    }

    public function store(VitalCreateRequest $request)
    {
        $data = $request->validated();
        $this->vitalRepository->create($data);

        return redirect()->back()->with('success', 'Vital created successfully.');
    }

    public function destroy($id)
    {
        $this->vitalRepository->delete($id);

        return redirect()->route('vitals.index')->with('success', 'Vital deleted successfully.');
    }
}
