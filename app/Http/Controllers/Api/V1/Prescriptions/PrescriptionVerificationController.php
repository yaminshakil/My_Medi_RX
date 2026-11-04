<?php

namespace App\Http\Controllers\Api\V1\Prescriptions;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionQrCode;
use App\Services\Eprescriptions\PrescriptionQrService;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class PrescriptionVerificationController extends Controller
{
    public function verify(string $token)
    {
        $qr = PrescriptionQrCode::where('token', $token)
            ->first();

        if (!$qr || !$qr->isValid()) {
            abort(404, 'Invalid, expired or revoked prescription QR code.');
        }

        return redirect()->route('prescriptions.qr.pdf', [
            'token' => $token,
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

    /**
     * Generate and display prescription PDF.
     */
    public function pdf(string $token): Response
    {
        $qr = PrescriptionQrCode::where('token', $token)
            ->first();

        if (!$qr || !$qr->isValid()) {
            abort(404, 'Prescription QR code is invalid or expired.');
        }

        $prescription = $qr->prescription;

        // Load your actual prescription relationships here.
        $prescription->load([
            'patient',
            'doctor',
            'vitals', 'medications', 'medications.medicines'
        ]);

        $pdf = Pdf::loadView(
            'eprescriptions.prescription-dompdf-a4',
            compact('prescription')
        )->setPaper('a4', 'portrait');

        return $pdf->stream(
            'prescription-' . $prescription->id . '.pdf'
        );
    }
}
