<?php

namespace App\Http\Controllers\Api\V1\Prescriptions;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionQrCode;
use App\Services\Eprescriptions\PrescriptionQrService;
use Illuminate\Http\JsonResponse;

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

    public function getQr(Prescription $prescription): JsonResponse
    {
        // Apply your existing prescription authorization policy here.

        $qr = PrescriptionQrCode::where(
            'prescription_id',
            $prescription->id
        )->latest()->first();

        if (!$qr || ($qr->expires_at && $qr->expires_at->isPast())) {
            return response()->json([
                'message' => 'QR code not found or expired.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Prescription QR retrieved successfully.',
            'data' => [
                'token' => $qr->token,
                'verification_url' => $qr->verification_url,
                'qr_code' => $qr->qr_code,
                'expires_at' => $qr->expires_at,
            ],
        ]);
    }
}
