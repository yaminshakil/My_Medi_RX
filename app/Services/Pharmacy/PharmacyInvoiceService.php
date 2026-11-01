<?php

namespace App\Services\Pharmacy;

use App\Models\PharmacyPrescriptionOrder;
use App\Models\Setting;

class PharmacyInvoiceService
{
    /**
     * @return array{
     *     invoiceNumber: string,
     *     currencyCode: string,
     *     currencySymbol: string,
     *     subtotal: string,
     *     discountTotal: string,
     *     total: string,
     *     lines: array<int, array{
     *         medicine: string,
     *         strength: ?string,
     *         quantity: int,
     *         regularPrice: string,
     *         discountPercentage: string,
     *         discountedPrice: string,
     *         lineTotal: string
     *     }>
     * }
     */
    public function present(PharmacyPrescriptionOrder $order): array
    {
        $subtotalCents = 0;
        $totalCents = 0;
        $lines = [];

        foreach ($order->dispenses as $dispense) {
            $regularPriceCents = $this->toMinorUnits((string) $dispense->unit_regular_price);
            $discountBasisPoints = $this->percentageToBasisPoints((string) $dispense->discount_percentage);
            $discountedPriceCents = intdiv(
                ($regularPriceCents * (10000 - $discountBasisPoints)) + 5000,
                10000,
            );
            $quantity = $dispense->quantity;
            $subtotalCents += $regularPriceCents * $quantity;
            $totalCents += $discountedPriceCents * $quantity;

            $lines[] = [
                'medicine'           => $dispense->inventoryItem->medicine_name,
                'strength'           => $dispense->inventoryItem->strength,
                'quantity'           => $quantity,
                'regularPrice'       => $this->formatMinorUnits($regularPriceCents),
                'discountPercentage' => (string) $dispense->discount_percentage,
                'discountedPrice'    => $this->formatMinorUnits($discountedPriceCents),
                'lineTotal'          => $this->formatMinorUnits($discountedPriceCents * $quantity),
            ];
        }

        return [
            'invoiceNumber'  => 'PH-INV-'.str_pad((string) $order->id, 8, '0', STR_PAD_LEFT),
            'currencyCode'   => (string) Setting::getValue('currency', 'USD'),
            'currencySymbol' => (string) Setting::getValue('currency_symbol', '$'),
            'subtotal'       => $this->formatMinorUnits($subtotalCents),
            'discountTotal'  => $this->formatMinorUnits($subtotalCents - $totalCents),
            'total'          => $this->formatMinorUnits($totalCents),
            'lines'          => $lines,
        ];
    }

    private function toMinorUnits(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function percentageToBasisPoints(string $percentage): int
    {
        [$whole, $fraction] = array_pad(explode('.', $percentage, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function formatMinorUnits(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
