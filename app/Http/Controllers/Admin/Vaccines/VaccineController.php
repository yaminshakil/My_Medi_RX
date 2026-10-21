<?php

namespace App\Http\Controllers\Admin\Vaccines;

use App\Http\Controllers\Controller;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Services\Eprescriptions\MedicineService;

class VaccineController extends Controller
{
    protected MedicineService $medicineService;

    public function __construct(MedicineService $medicineService)
    {
        $this->medicineService = $medicineService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['all', 'active', 'inactive'])],
        ]);

        $vaccines = Vaccine::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('manufacturer_id', 'like', "%{$search}%");
                });
            })
            ->when(
                ($filters['status'] ?? 'all') !== 'all',
                fn ($query) => $query->where(
                    'is_active',
                    ($filters['status'] ?? 'all') === 'active'
                )
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $manufacturers = $this->medicineService->getManufacturer();

        $manufacturers = $manufacturers->map(function ($manufacturer) {
            return [
                'name' => $manufacturer->company_name,
                'id' => $manufacturer->id,
            ];
        });

        return Inertia::render('Admin/Vaccines/Index', [
            'vaccines' => $vaccines,
            'manufacturers' => $manufacturers,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? 'all',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('vaccines', 'code'),
            ],
            'manufacturer_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        Vaccine::create($data);

        return back()->with('success', 'Vaccine created successfully.');
    }

    public function update(Request $request, Vaccine $vaccine): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('vaccines', 'code')->ignore($vaccine->id),
            ],
            'manufacturer_id' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $vaccine->update($data);

        return back()->with('success', 'Vaccine updated successfully.');
    }

    public function destroy(Vaccine $vaccine): RedirectResponse
    {
        $vaccine->delete();

        return back()->with('success', 'Vaccine deleted successfully.');
    }
}
