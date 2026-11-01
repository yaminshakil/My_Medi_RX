<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportPharmacyInventoryCsvRequest;
use App\Models\PharmacyInventoryItem;
use App\Services\Pharmacy\PharmacyInventoryCsvService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PharmacyInventoryCsvController extends Controller
{
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, PharmacyInventoryCsvService::HEADERS, ',', '"', '');
            fclose($output);
        }, 'pharmacy-inventory-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->inventoryQuery($request);

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, PharmacyInventoryCsvService::HEADERS, ',', '"', '');

            foreach ($query->lazy(500) as $item) {
                fputcsv($output, [
                    $item->pharmacy_id,
                    $this->spreadsheetSafe($item->medicine_name),
                    $this->spreadsheetSafe($item->generic_name),
                    $this->spreadsheetSafe($item->strength),
                    $item->regular_price,
                    $item->discount_percentage,
                    $item->stock_quantity,
                    $item->reorder_level,
                    $item->expiry_date?->toDateString(),
                ], ',', '"', '');
            }

            fclose($output);
        }, 'pharmacy-inventory.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(
        ImportPharmacyInventoryCsvRequest $request,
        PharmacyInventoryCsvService $inventoryCsv,
    ): RedirectResponse {
        $result = $inventoryCsv->import($request->file('file'), $request->user());

        return to_route('admin.inventory.index')->with(
            'success',
            "Inventory imported: {$result['created']} item(s) created and {$result['updated']} updated. Existing quantities were replaced with CSV quantities.",
        );
    }

    private function inventoryQuery(Request $request): Builder
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('Admin');
        $pharmacy = $user->pharmacy;

        abort_unless($isAdmin || ($user->hasRole('Pharmacy') && $pharmacy), 403);

        $filters = $request->validate([
            'search'      => ['nullable', 'string', 'max:100'],
            'pharmacy_id' => $isAdmin
                ? ['nullable', 'integer', 'exists:pharmacies,id']
                : ['prohibited'],
            'stock_status' => ['nullable', Rule::in(['all', 'low', 'out', 'expiring', 'expired'])],
        ]);
        $search = $filters['search'] ?? '';
        $stockStatus = $filters['stock_status'] ?? 'all';

        return PharmacyInventoryItem::query()
            ->when(! $isAdmin, fn (Builder $query) => $query->where('pharmacy_id', $pharmacy->id))
            ->when(
                $isAdmin && ($filters['pharmacy_id'] ?? '') !== '',
                fn (Builder $query) => $query->where('pharmacy_id', $filters['pharmacy_id']),
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
            ->orderBy('id');
    }

    private function spreadsheetSafe(?string $value): ?string
    {
        if ($value !== null && preg_match('/^[\s\x00-\x1f]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
