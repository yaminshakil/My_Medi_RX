<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class MedicationScanController extends Controller
{
    private function patient(Request $request): Patient
    {
        return Patient::where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    public function qr(Request $request)
    {
        $data = $request->validate([
            'qr_token' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $payload = json_decode(
                Crypt::decryptString($data['qr_token']),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Invalid or expired prescription QR code.',
            ], 422);
        }

        $prescriptionMedicineId = $payload['prescription_medicine_id'] ?? null;

        if (!$prescriptionMedicineId) {
            return response()->json([
                'message' => 'QR code does not contain a valid prescription reference.',
            ], 422);
        }

        $prescriptionMedicine = $this->patient($request)
            ->prescriptionMedicines()
            ->with('medicine')
            ->where('prescription_medicines.id', $prescriptionMedicineId)
            ->first();

        if (!$prescriptionMedicine) {
            return response()->json([
                'message' => 'Prescription not found for this patient.',
            ], 404);
        }

        // Return verified details for confirmation.
        // Create the patient medication only after confirmation,
        // unless your workflow explicitly authorizes automatic import.
        return response()->json([
            'message' => 'Prescription verified. Confirm before importing.',
            'data' => $prescriptionMedicine,
        ]);
    }

    public function ocr(Request $request)
    {
        $data = $request->validate([
            'prescription_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],
        ]);

        // Integrate an OCR provider here. Do not treat extracted
        // text as a verified prescription or dosage instruction.
        //
        // Example next step:
        // $result = app(PrescriptionOcrService::class)
        //     ->extract($data['prescription_image']);

        return response()->json([
            'message' => 'Image validated. Configure an OCR provider to extract prescription text.',
            'ocr_configured' => false,
        ], 501);
    }
}
