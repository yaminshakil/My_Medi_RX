<?php

namespace App\Http\Controllers\Admin\Hospitals;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\HospitalVerificationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HospitalVerificationController extends Controller
{
    public function show(string $uuid)
    {
        $hospital = Hospital::query()
            ->with([
                'hospitalType',
                'verificationDocuments.verifier',
                'verifier',
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render(
            'Admin/Hospitals/Verification',
            [
                'hospital' => $hospital,
                'documentTypes' => config(
                    'hospital.document_types'
                ),
            ]
        );
    }

    public function uploadDocument(Request $request, string $uuid)
    {
        $hospital = Hospital::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'document_type' => [
                'required',
                'string',
                'in:' . implode(
                    ',',
                    array_keys(config('hospital.document_types'))
                ),
            ],

            'document_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'document_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'document' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',
            ],

            'issued_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:issued_at',
            ],
        ]);

        $path = $request
            ->file('document')
            ->store('hospitals/verification-documents', 'public');

        $hospital->verificationDocuments()->create([
            'document_type' => $validated['document_type'],
            'document_title' => $validated['document_title'] ?? null,
            'document_number' => $validated['document_number'] ?? null,
            'document_path' => $path,
            'issued_at' => $validated['issued_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'verification_status' => 'pending',
        ]);

        if ($hospital->verification_status === 'pending') {
            $hospital->update([
                'verification_status' => 'under_review',
            ]);
        }

        return back()->with(
            'success',
            'Verification document uploaded successfully.'
        );
    }

    public function approveDocument(int $id)
    {
        $document = HospitalVerificationDocument::findOrFail($id);

        $document->update([
            'verification_status' => 'approved',
            'rejection_reason' => null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $this->updateHospitalVerificationStatus(
            $document->hospital
        );

        return back()->with(
            'success',
            'Document approved successfully.'
        );
    }

    public function rejectDocument(Request $request, int $id)
    {
        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $document = HospitalVerificationDocument::findOrFail($id);

        $document->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $document->hospital->update([
            'verification_status' => 'rejected',
            'status' => 0,
        ]);

        return back()->with(
            'success',
            'Document rejected.'
        );
    }

    public function approveHospital(string $uuid)
    {
        $hospital = Hospital::with('verificationDocuments')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $hasDocuments = $hospital
            ->verificationDocuments
            ->isNotEmpty();

        if (!$hasDocuments) {
            return back()->withErrors([
                'verification' =>
                    'At least one verification document is required.',
            ]);
        }

        $hasPending = $hospital
            ->verificationDocuments
            ->contains(
                fn ($document) =>
                    $document->verification_status === 'pending'
            );

        $hasRejected = $hospital
            ->verificationDocuments
            ->contains(
                fn ($document) =>
                    $document->verification_status === 'rejected'
            );

        if ($hasPending) {
            return back()->withErrors([
                'verification' =>
                    'All submitted documents must be reviewed first.',
            ]);
        }

        if ($hasRejected) {
            return back()->withErrors([
                'verification' =>
                    'Rejected documents must be replaced or resolved first.',
            ]);
        }

        $hospital->update([
            'verification_status' => 'approved',
            'status' => 1,
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'verification_note' => null,
        ]);

        return back()->with(
            'success',
            'Hospital approved and activated successfully.'
        );
    }

    public function rejectHospital(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'verification_note' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $hospital = Hospital::where('uuid', $uuid)
            ->firstOrFail();

        $hospital->update([
            'verification_status' => 'rejected',
            'status' => 0,
            'verification_note' => $validated['verification_note'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with(
            'success',
            'Hospital registration rejected.'
        );
    }

    public function suspendHospital(string $uuid)
    {
        $hospital = Hospital::where('uuid', $uuid)
            ->firstOrFail();

        $hospital->update([
            'verification_status' => 'suspended',
            'status' => 0,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with(
            'success',
            'Hospital suspended successfully.'
        );
    }

    public function destroyDocument(int $id)
    {
        $document = HospitalVerificationDocument::findOrFail($id);

        Storage::disk('public')->delete(
            $document->document_path
        );

        $document->delete();

        return back()->with(
            'success',
            'Document deleted successfully.'
        );
    }

    private function updateHospitalVerificationStatus(
        Hospital $hospital
    ): void {
        $documents = $hospital
            ->verificationDocuments()
            ->get();

        if ($documents->contains(
            fn ($document) =>
                $document->verification_status === 'rejected'
        )) {
            $hospital->update([
                'verification_status' => 'rejected',
                'status' => 0,
            ]);

            return;
        }

        if (
            $documents->isNotEmpty() &&
            $documents->every(
                fn ($document) =>
                    $document->verification_status === 'approved'
            )
        ) {
            $hospital->update([
                'verification_status' => 'under_review',
            ]);

            return;
        }

        $hospital->update([
            'verification_status' => 'under_review',
        ]);
    }
}
