<?php

namespace App\Services\Pharmacy;

use App\Exceptions\PharmacyConflictException;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PharmacyService
{
    /**
     * @param array{
     *     name: string,
     *     phone?: string|null,
     *     email?: string|null,
     *     description?: string|null,
     *     address: array<string, mixed>
     * } $data
     */
    public function register(User $owner, array $data): Pharmacy
    {
        if ($owner->pharmacy()->exists()) {
            throw new PharmacyConflictException('A pharmacy is already registered for this account.');
        }

        return DB::transaction(function () use ($owner, $data): Pharmacy {
            $pharmacy = $owner->pharmacy()->create([
                'name'        => $data['name'],
                'slug'        => Str::slug($data['name']).'-'.Str::lower(Str::random(8)),
                'phone'       => $data['phone'] ?? null,
                'email'       => $data['email'] ?? null,
                'description' => $data['description'] ?? null,
                'status'      => 'pending',
            ]);

            $pharmacy->address()->create($data['address']);

            return $pharmacy->load('address');
        });
    }

    /**
     * @param array{
     *     license_number: string,
     *     license_type: string,
     *     issued_date?: string|null,
     *     expiry_date?: string|null
     * } $data
     */
    public function uploadLicenseDocument(
        User $owner,
        array $data,
        UploadedFile $document,
    ): PharmacyLicense {
        $pharmacy = $owner->pharmacy()->first();

        if ($pharmacy === null) {
            throw new PharmacyConflictException('Register a pharmacy before uploading its license document.');
        }

        $path = $document->store('pharmacies/licenses', 'local');

        if (! is_string($path)) {
            throw new RuntimeException('The pharmacy license document could not be stored.');
        }

        try {
            return DB::transaction(fn (): PharmacyLicense => $pharmacy->licenses()->create([
                'license_number' => $data['license_number'],
                'license_type'   => $data['license_type'],
                'issued_date'    => $data['issued_date'] ?? null,
                'expiry_date'    => $data['expiry_date'] ?? null,
                'document'       => $path,
                'status'         => 'pending',
            ]));
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }
}
