<?php

namespace App\Http\Controllers\Api\V1\Prescriptions;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionQrCode;
use App\Services\Eprescriptions\PrescriptionQrService;

class PrescriptionVerificationController extends Controller
{
    public function verify(string $token)
    {
        $qr = PrescriptionQrCode::with('prescription')
            ->where('token', $token)
            ->first();

        if (! $qr || ! $qr->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription QR code is invalid, expired or revoked.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prescription QR code is valid.',
            'data'    => [
                'prescription_id'     => $qr->prescription_id,
                'status'              => 'valid',
                'verification_status' => 'verified',
            ],
        ]);
    }

    public function generateQr(
        Prescription $prescription,
        PrescriptionQrService $qrService
    ) {
        if ($prescription->status !== 'issued') {
            return response()->json([
                'success' => false,
                'message' => 'Only finalized prescriptions can generate QR codes.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $qrService->generate($prescription),
        ]);
    }
}
