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
        // Check whether an active QR already exists
        $qr = PrescriptionQrCode::where('prescription_id', $prescription->id)
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        // Return existing QR if available
        if ($qr) {
            return [
                'token'            => $qr->token,
                'verification_url' => $qr->verification_url,
                'qr_code'          => $qr->qr_code,
                'expires_at'       => $qr->expires_at,
            ];
        }

        // Generate a new secure token
        $token = (string) Str::uuid();

        // Generate verification URL
        $url = route('prescriptions.verify', [
            'token' => $token,
        ]);

        // Generate QR image
        $qrImage = QrCode::size(300)
            ->margin(3)
            ->errorCorrection('H')
            ->generateDataUri($url);

        // Save QR information
        $qr = PrescriptionQrCode::create([
            'prescription_id'  => $prescription->id,
            'token'            => $token,
            'verification_url' => $url,
            'qr_code'          => $qrImage,
            'expires_at'       => now()->addYear(),
            'revoked_at'       => null,
        ]);

        return [
            'token'            => $qr->token,
            'verification_url' => $qr->verification_url,
            'qr_code'          => $qr->qr_code,
            'expires_at'       => $qr->expires_at,
        ];
    }
}
