<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Modules\BloodBank\Enums\StorageUnitType;
use App\Modules\BloodBank\Http\Requests\StorageUnitRequest;
use App\Modules\BloodBank\Models\StorageUnit;
use App\Modules\BloodBank\Repositories\Contracts\StorageUnitRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorageUnitController extends Controller
{
    public function __construct(private readonly StorageUnitRepositoryInterface $storageUnits) {}

    public function index(Request $request): Response
    {
        $hospitalQuery = Hospital::query()
            ->select('id', 'hospital_name')
            ->orderBy('hospital_name');

        if (! $request->user()->is_admin) {
            $hospitalQuery->whereKey($request->user()->hospital?->id ?? 0);
        }

        return Inertia::render('Admin/BloodBank/StorageUnits/Index', [
            'storage_units' => $this->storageUnits->paginate()->through(fn (StorageUnit $unit): array => [
                'id'            => $unit->id,
                'name'          => $unit->name,
                'location'      => $unit->location,
                'type'          => $unit->type,
                'min_temp_c'    => $unit->min_temp_c,
                'max_temp_c'    => $unit->max_temp_c,
                'capacity'      => $unit->capacity,
                'is_active'     => $unit->is_active,
                'bags_count'    => $unit->bags_count,
                'hospital_id'   => $unit->hospital_id,
                'hospital_name' => $unit->hospital?->hospital_name,
            ]),
            'type_options' => array_map(fn (StorageUnitType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ], StorageUnitType::cases()),
            'hospitals' => $hospitalQuery->get(),
        ]);
    }

    public function store(StorageUnitRequest $request): RedirectResponse
    {
        $this->storageUnits->create($request->validated());

        return back()->with('success', 'Storage unit created.');
    }

    public function update(StorageUnitRequest $request, StorageUnit $storageUnit): RedirectResponse
    {
        $this->storageUnits->update($storageUnit, $request->validated());

        return back()->with('success', 'Storage unit updated.');
    }

    public function destroy(StorageUnit $storageUnit): RedirectResponse
    {
        if ($storageUnit->bags()->exists()) {
            return back()->with('error', 'Storage units with blood bags cannot be deleted. Deactivate the unit instead.');
        }

        $this->storageUnits->delete($storageUnit);

        return back()->with('success', 'Storage unit deleted.');
    }
}
