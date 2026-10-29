<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\SavePharmacyLicenseRequest;
use App\Models\Pharmacy;
use App\Models\PharmacyLicense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PharmacyLicenseController extends Controller
{
    public function store(SavePharmacyLicenseRequest $request, Pharmacy $pharmacy): RedirectResponse
    {
        $validated = $request->validated();
        $validated['document'] = $request->file('document')
            ->store('pharmacies/licenses', 'local');

        $pharmacy->licenses()->create($validated);

        return back()->with('success', 'License added and queued for review.');
    }

    public function update(
        SavePharmacyLicenseRequest $request,
        Pharmacy $pharmacy,
        PharmacyLicense $license,
    ): RedirectResponse {
        $validated = $request->validated();
        $oldDocument = $license->document;
        $newDocument = $request->file('document')?->store('pharmacies/licenses', 'local');

        $license->update([
            ...$validated,
            'document'         => $newDocument ?? $oldDocument,
            'status'           => 'pending',
            'verified_at'      => null,
            'verified_by'      => null,
            'rejection_reason' => null,
        ]);

        if ($newDocument !== null) {
            Storage::disk('local')->delete($oldDocument);
        }

        return back()->with('success', 'License updated and returned to review.');
    }

    public function destroy(Pharmacy $pharmacy, PharmacyLicense $license): RedirectResponse
    {
        $document = $license->document;
        $license->delete();
        Storage::disk('local')->delete($document);

        return back()->with('success', 'License deleted.');
    }

    public function download(Pharmacy $pharmacy, PharmacyLicense $license): StreamedResponse
    {
        abort_if(
            blank($license->document) || ! Storage::disk('local')->exists($license->document),
            404,
        );

        return Storage::disk('local')->download($license->document);
    }

    public function review(Request $request, Pharmacy $pharmacy, PharmacyLicense $license): RedirectResponse
    {
        $validated = $request->validate([
            'status'           => ['required', 'in:verified,rejected'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === 'verified' && $license->expiry_date?->isBefore(today())) {
            return back()->withErrors([
                'license' => 'An expired license cannot be verified.',
            ]);
        }

        $license->update([
            'status'           => $validated['status'],
            'verified_at'      => now(),
            'verified_by'      => $request->user()->id,
            'rejection_reason' => $validated['status'] === 'rejected'
                ? $validated['rejection_reason']
                : null,
        ]);

        return back()->with('success', 'License review saved.');
    }
}
