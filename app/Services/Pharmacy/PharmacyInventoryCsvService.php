<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy;
use App\Models\PharmacyInventoryItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PharmacyInventoryCsvService
{
    public const HEADERS = [
        'pharmacy_id',
        'medicine_name',
        'generic_name',
        'strength',
        'regular_price',
        'discount_percentage',
        'stock_quantity',
        'reorder_level',
        'expiry_date',
    ];

    /**
     * @return array{created: int, updated: int}
     */
    public function import(UploadedFile $file, User $user): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages(['file' => 'The uploaded CSV could not be read.']);
        }

        try {
            $headers = fgetcsv($handle, null, ',', '"', '');

            if ($headers === false) {
                throw ValidationException::withMessages(['file' => 'The CSV file is empty.']);
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $headers = array_map(fn ($header): string => trim((string) $header), $headers);

            if ($headers !== self::HEADERS) {
                throw ValidationException::withMessages([
                    'file' => 'CSV columns must match the downloaded inventory template and remain in the same order.',
                ]);
            }

            $rows = [];
            $errors = [];
            $lineNumber = 1;
            $seenItems = [];

            while (($values = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $lineNumber++;

                if (count(array_filter($values, fn ($value): bool => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                if (count($values) !== count(self::HEADERS)) {
                    $errors[] = "Row {$lineNumber} must contain exactly ".count(self::HEADERS).' columns.';

                    continue;
                }

                $row = array_combine(self::HEADERS, array_map(
                    fn ($value): string => trim((string) $value),
                    $values,
                ));
                $row['generic_name'] = $row['generic_name'] !== '' ? $row['generic_name'] : null;
                $row['strength'] = $row['strength'] !== '' ? $row['strength'] : null;
                $row['expiry_date'] = $row['expiry_date'] !== '' ? $row['expiry_date'] : null;

                $validator = Validator::make($row, [
                    'pharmacy_id'         => ['required', 'integer', 'exists:pharmacies,id'],
                    'medicine_name'       => ['required', 'string', 'max:255'],
                    'generic_name'        => ['nullable', 'string', 'max:255'],
                    'strength'            => ['nullable', 'string', 'max:100'],
                    'regular_price'       => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
                    'discount_percentage' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
                    'stock_quantity'      => ['required', 'integer', 'min:0', 'max:10000000'],
                    'reorder_level'       => ['required', 'integer', 'min:0', 'max:10000000'],
                    'expiry_date'         => ['nullable', 'date'],
                ]);

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $message) {
                        $errors[] = "Row {$lineNumber}: {$message}";
                    }

                    continue;
                }

                $row['pharmacy_id'] = (int) $row['pharmacy_id'];

                if (! $user->hasRole('Admin') && $row['pharmacy_id'] !== $user->pharmacy?->id) {
                    $errors[] = "Row {$lineNumber}: You can import inventory only for your own pharmacy.";

                    continue;
                }

                $itemKey = implode('|', [
                    $row['pharmacy_id'],
                    mb_strtolower($row['medicine_name']),
                    mb_strtolower($row['generic_name'] ?? ''),
                    mb_strtolower($row['strength'] ?? ''),
                ]);

                if (isset($seenItems[$itemKey])) {
                    $errors[] = "Row {$lineNumber}: This medicine appears more than once for the same pharmacy.";

                    continue;
                }

                $seenItems[$itemKey] = true;
                $rows[] = $row;

                if (count($rows) > 2000) {
                    $errors[] = 'A single import cannot contain more than 2,000 inventory rows.';

                    break;
                }
            }
        } finally {
            fclose($handle);
        }

        if ($rows === [] && $errors === []) {
            $errors[] = 'The CSV file contains no inventory rows.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => array_slice($errors, 0, 100)]);
        }

        return DB::transaction(function () use ($rows): array {
            $pharmacies = Pharmacy::query()
                ->whereKey(collect($rows)->pluck('pharmacy_id')->unique()->sort()->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $created = 0;
            $updated = 0;

            foreach ($rows as $row) {
                $query = PharmacyInventoryItem::query()
                    ->where('pharmacy_id', $row['pharmacy_id'])
                    ->where('medicine_name', $row['medicine_name'])
                    ->when(
                        $row['generic_name'] === null,
                        fn ($query) => $query->whereNull('generic_name'),
                        fn ($query) => $query->where('generic_name', $row['generic_name']),
                    )
                    ->when(
                        $row['strength'] === null,
                        fn ($query) => $query->whereNull('strength'),
                        fn ($query) => $query->where('strength', $row['strength']),
                    )
                    ->lockForUpdate();

                $item = $query->first();
                $attributes = [
                    'medicine_name'       => $row['medicine_name'],
                    'generic_name'        => $row['generic_name'],
                    'strength'            => $row['strength'],
                    'regular_price'       => $row['regular_price'],
                    'discount_percentage' => $row['discount_percentage'],
                    'stock_quantity'      => $row['stock_quantity'],
                    'reorder_level'       => $row['reorder_level'],
                    'expiry_date'         => $row['expiry_date'],
                ];

                if ($item) {
                    $item->update($attributes);
                    $updated++;

                    continue;
                }

                $pharmacy = $pharmacies->get($row['pharmacy_id']);

                if ($pharmacy === null) {
                    throw ValidationException::withMessages([
                        'file' => 'A selected pharmacy no longer exists. No inventory was imported.',
                    ]);
                }

                $pharmacy->inventoryItems()->create($attributes);
                $created++;
            }

            return compact('created', 'updated');
        });
    }
}
