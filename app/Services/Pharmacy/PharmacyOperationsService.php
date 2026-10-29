<?php

namespace App\Services\Pharmacy;

use App\Exceptions\PharmacyOperationException;
use App\Models\Pharmacy;
use App\Models\PharmacyDispense;
use App\Models\PharmacyInventoryItem;
use App\Models\PharmacyPrescriptionOrder;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PharmacyOperationsService
{
    public function approvedPharmacies(): LengthAwarePaginator
    {
        return Pharmacy::query()
            ->approved()
            ->select(['id', 'name', 'phone', 'email'])
            ->with('address:id,pharmacy_id,address_line_1,address_line_2,division,district,upazila,postal_code,latitude,longitude')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(50);
    }

    /**
     * @return Collection<int, PharmacyInventoryItem>
     */
    public function inventory(User $owner): Collection
    {
        return $this->pharmacyFor($owner)
            ->inventoryItems()
            ->orderBy('medicine_name')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param array{
     *     medicine_name: string,
     *     generic_name?: string|null,
     *     strength?: string|null,
     *     stock_quantity: int,
     *     reorder_level: int,
     *     expiry_date?: string|null
     * } $data
     */
    public function addInventoryItem(User $owner, array $data): PharmacyInventoryItem
    {
        return $this->pharmacyFor($owner)->inventoryItems()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateInventoryItem(
        User $owner,
        int $inventoryItemId,
        array $data,
    ): PharmacyInventoryItem {
        $item = $this->pharmacyFor($owner)
            ->inventoryItems()
            ->findOrFail($inventoryItemId);

        $item->update($data);

        return $item->refresh();
    }

    /**
     * @return Collection<int, PharmacyPrescriptionOrder>
     */
    public function prescriptionOrders(User $owner): Collection
    {
        return $this->approvedPharmacyFor($owner)
            ->prescriptionOrders()
            ->with([
                'prescription:id,uuid,prescription_number,created_at',
                'prescription.medications:id,prescription_id,medicine_id,strength,dosage,duration',
                'prescription.medications.medicine:id,brand_name,generic_name,strength',
            ])
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();
    }

    public function sharePrescriptionWithPharmacy(
        User $patient,
        string $prescriptionUuid,
        int $pharmacyId,
    ): PharmacyPrescriptionOrder {
        try {
            return DB::transaction(function () use ($patient, $prescriptionUuid, $pharmacyId): PharmacyPrescriptionOrder {
                $prescription = Prescription::query()
                    ->where('uuid', $prescriptionUuid)
                    ->whereHas('patient', fn ($query) => $query->where('user_id', $patient->id))
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($prescription->status !== 'issued') {
                    throw new PharmacyOperationException(
                        'Only issued prescriptions can be shared with a pharmacy.',
                    );
                }

                $pharmacy = Pharmacy::query()
                    ->whereKey($pharmacyId)
                    ->where('status', 'approved')
                    ->firstOrFail();

                return $pharmacy->prescriptionOrders()->create([
                    'prescription_id' => $prescription->id,
                    'status'          => 'received',
                    'received_at'     => now(),
                ]);
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw new PharmacyOperationException(
                'This prescription has already been shared with a pharmacy.',
            );
        }
    }

    public function startPrescriptionProcessing(User $owner, int $orderId): PharmacyPrescriptionOrder
    {
        $pharmacy = $this->approvedPharmacyFor($owner);

        return DB::transaction(function () use ($pharmacy, $orderId): PharmacyPrescriptionOrder {
            $order = $pharmacy->prescriptionOrders()
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status !== 'received') {
                throw new PharmacyOperationException(
                    'Only received prescriptions can be moved into processing.',
                );
            }

            $order->update(['status' => 'processing']);

            return $order->refresh();
        });
    }

    /**
     * @param  list<array{inventory_item_id: int, quantity: int}>  $items
     */
    public function dispensePrescription(
        User $owner,
        int $orderId,
        array $items,
    ): PharmacyPrescriptionOrder {
        $pharmacy = $this->approvedPharmacyFor($owner);

        return DB::transaction(function () use ($owner, $pharmacy, $orderId, $items): PharmacyPrescriptionOrder {
            $order = $pharmacy->prescriptionOrders()
                ->lockForUpdate()
                ->findOrFail($orderId);

            if (! in_array($order->status, ['received', 'processing'], true)) {
                throw new PharmacyOperationException(
                    'Only received or processing prescriptions can be dispensed.',
                );
            }

            foreach ($items as $itemData) {
                $inventoryItem = $pharmacy->inventoryItems()
                    ->lockForUpdate()
                    ->findOrFail($itemData['inventory_item_id']);

                if ($inventoryItem->stock_quantity < $itemData['quantity']) {
                    throw new PharmacyOperationException(
                        "Insufficient stock for {$inventoryItem->medicine_name}.",
                    );
                }

                $inventoryItem->decrement('stock_quantity', $itemData['quantity']);

                PharmacyDispense::query()->create([
                    'pharmacy_id'                    => $pharmacy->id,
                    'pharmacy_prescription_order_id' => $order->id,
                    'pharmacy_inventory_item_id'     => $inventoryItem->id,
                    'dispensed_by'                   => $owner->id,
                    'quantity'                       => $itemData['quantity'],
                    'dispensed_at'                   => now(),
                ]);
            }

            $order->update([
                'status'       => 'dispensed',
                'dispensed_at' => now(),
            ]);

            return $order->refresh();
        });
    }

    private function pharmacyFor(User $owner): Pharmacy
    {
        $pharmacy = $owner->pharmacy()->first();

        if ($pharmacy === null) {
            throw new PharmacyOperationException(
                'Register a pharmacy before using pharmacy operations.',
            );
        }

        return $pharmacy;
    }

    private function approvedPharmacyFor(User $owner): Pharmacy
    {
        $pharmacy = $this->pharmacyFor($owner);

        if (! $pharmacy->isApproved()) {
            throw new PharmacyOperationException(
                'Pharmacy prescription operations require an approved pharmacy.',
                403,
            );
        }

        return $pharmacy;
    }
}
