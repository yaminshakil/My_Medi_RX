<?php

namespace App\Services\Eprescriptions;

use App\Models\Prescription;
use App\Models\PrescriptionQrCode;
use Illuminate\Support\Str;
use Manoar\QrCode\Facades\QrCode;

class PrescriptionQrService
{
    public function generate(Prescription $prescription): array
    {
        $qr = PrescriptionQrCode::firstOrCreate(
            [
                'prescription_id' => $prescription->id,
                'revoked_at'      => null,
            ],
            [
                'token'      => (string) Str::uuid(),
                'expires_at' => now()->addYear(),
            ]
        );

        $url = route('prescriptions.verify', [
            'token' => $qr->token,
        ]);

        $qrImage = QrCode::size(300)
            ->margin(3)
            ->errorCorrection('H')
            ->generateDataUri($url);

        return [
            'token'            => $qr->token,
            'verification_url' => $url,
            'qr_code'          => $qrImage,
            'expires_at'       => $qr->expires_at,
        ];
    }
}
