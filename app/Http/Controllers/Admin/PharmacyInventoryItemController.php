<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustPharmacyInventoryStockRequest;
use App\Http\Requests\Admin\SavePharmacyInventoryItemRequest;
use App\Models\Pharmacy;
use App\Models\PharmacyInventoryItem;
use App\Services\PharmacyInventoryAdminService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyInventoryItemController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('Admin');
        $userPharmacy = $user->pharmacy;

        if (! $isAdmin && (! $user->hasRole('Pharmacy') || ! $userPharmacy)) {
            abort(403, 'You are not associated with a pharmacy.');
        }

        $filters = $request->validate([
            'search'      => ['nullable', 'string', 'max:100'],
            'pharmacy_id' => $isAdmin
                ? ['nullable', 'integer', 'exists:pharmacies,id']
                : ['prohibited'],
            'stock_status' => ['nullable', Rule::in(['all', 'low', 'out', 'expiring', 'expired'])],
            'per_page'     => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $search = $filters['search'] ?? '';
        $pharmacyId = $filters['pharmacy_id'] ?? '';
        $stockStatus = $filters['stock_status'] ?? 'all';
        $perPage = $filters['per_page'] ?? 15;

        $inventoryQuery = PharmacyInventoryItem::query()
            ->when(
                ! $isAdmin,
                fn (Builder $query) => $query->where('pharmacy_id', $userPharmacy->id)
            );

        $items = (clone $inventoryQuery)
            ->with('pharmacy:id,name')
            ->when(
                $isAdmin && $pharmacyId !== '',
                fn (Builder $query) => $query->where('pharmacy_id', $filters['pharmacy_id'])
            )
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('medicine_name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('strength', 'like', "%{$search}%")
                        ->orWhereHas('pharmacy', fn (Builder $pharmacyQuery) => $pharmacyQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($stockStatus === 'low', fn (Builder $query) => $query->whereColumn('stock_quantity', '<=', 'reorder_level'))
            ->when($stockStatus === 'out', fn (Builder $query) => $query->where('stock_quantity', 0))
            ->when($stockStatus === 'expiring', fn (Builder $query) => $query
                ->whereBetween('expiry_date', [today()->toDateString(), today()->addDays(30)->toDateString()]))
            ->when($stockStatus === 'expired', fn (Builder $query) => $query
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', today()->toDateString()))
            ->orderBy('medicine_name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (PharmacyInventoryItem $item): array => [
                'id'             => $item->id,
                'pharmacy_id'    => $item->pharmacy_id,
                'medicine_name'  => $item->medicine_name,
                'generic_name'   => $item->generic_name,
                'strength'       => $item->strength,
                'stock_quantity' => $item->stock_quantity,
                'reorder_level'  => $item->reorder_level,
                'expiry_date'    => $item->expiry_date?->toDateString(),
                'pharmacy'       => [
                    'id'   => $item->pharmacy->id,
                    'name' => $item->pharmacy->name,
                ],
            ]);

        return Inertia::render('Admin/Inventory/Index', [
            'inventory'  => $items,
            'isAdmin'    => $isAdmin,
            'pharmacies' => Pharmacy::query()
                ->select(['id', 'name'])
                ->when(
                    ! $isAdmin,
                    fn (Builder $query) => $query->whereKey($userPharmacy->id)
                )
                ->orderBy('name')
                ->orderBy('id')
                ->get(),
            'filters' => [
                'search'       => $search,
                'pharmacy_id'  => (string) $pharmacyId,
                'stock_status' => $stockStatus,
                'per_page'     => $perPage,
            ],
            'stats' => [
                'totalItems'        => (clone $inventoryQuery)->count(),
                'totalUnits'        => (clone $inventoryQuery)->sum('stock_quantity'),
                'lowStockItems'     => (clone $inventoryQuery)->whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
                'outOfStockItems'   => (clone $inventoryQuery)->where('stock_quantity', 0)->count(),
                'expiringSoonItems' => (clone $inventoryQuery)
                    ->whereBetween('expiry_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
                    ->count(),
                'expiredItems' => (clone $inventoryQuery)
                    ->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', today()->toDateString())
                    ->count(),
            ],
        ]);
    }

    public function store(SavePharmacyInventoryItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $pharmacy = $request->user()->hasRole('Admin')
            ? Pharmacy::query()->findOrFail($data['pharmacy_id'])
            : $request->user()->pharmacy;

        if (! $pharmacy) {
            abort(403, 'You are not associated with a pharmacy.');
        }

        $pharmacy->inventoryItems()->create([
            'medicine_name'  => $data['medicine_name'],
            'generic_name'   => $data['generic_name'] ?? null,
            'strength'       => $data['strength'] ?? null,
            'stock_quantity' => $data['stock_quantity'],
            'reorder_level'  => $data['reorder_level'],
            'expiry_date'    => $data['expiry_date'] ?? null,
        ]);

        return to_route('admin.inventory.index')->with('success', 'Inventory item added.');
    }

    public function update(
        SavePharmacyInventoryItemRequest $request,
        PharmacyInventoryItem $inventoryItem,
    ): RedirectResponse {
        $inventoryItem->update($request->safe()->only([
            'medicine_name',
            'generic_name',
            'strength',
            'reorder_level',
            'expiry_date',
        ]));

        return to_route('admin.inventory.index')->with('success', 'Inventory item updated.');
    }

    public function adjustStock(
        AdjustPharmacyInventoryStockRequest $request,
        PharmacyInventoryItem $inventoryItem,
        PharmacyInventoryAdminService $inventory,
    ): RedirectResponse {
        $data = $request->validated();
        $inventory->adjustStock(
            $inventoryItem,
            $data['direction'],
            $data['quantity'],
        );

        return back()->with('success', 'Stock quantity adjusted.');
    }

    public function destroy(Request $request, PharmacyInventoryItem $inventoryItem): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('Admin')
                || ($user->hasRole('Pharmacy') && $inventoryItem->pharmacy_id === $user->pharmacy?->id),
            403,
        );

        DB::transaction(function () use ($inventoryItem): void {
            $lockedItem = PharmacyInventoryItem::query()
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);

            if ($lockedItem->dispenses()->exists()) {
                throw ValidationException::withMessages([
                    'inventoryItem' => 'This item cannot be deleted because it has dispense history.',
                ]);
            }

            $lockedItem->delete();
        });

        return to_route('admin.inventory.index')->with('success', 'Inventory item deleted.');
    }
}
