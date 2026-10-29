<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\SavePharmacistRequest;
use App\Models\Pharmacist;
use App\Models\Pharmacy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PharmacistController extends Controller
{
    public function store(SavePharmacistRequest $request, Pharmacy $pharmacy): RedirectResponse
    {
        $validated = $request->validated();
        $validated['registration_document'] = $request->file('registration_document')
            ->store('pharmacies/pharmacists', 'local');

        DB::transaction(function () use ($pharmacy, $validated): void {
            if ($validated['is_primary'] ?? false) {
                $pharmacy->pharmacists()->update(['is_primary' => false]);
            }

            $pharmacy->pharmacists()->create($validated);
        });

        return back()->with('success', 'Pharmacist added and queued for review.');
    }

    public function update(
        SavePharmacistRequest $request,
        Pharmacy $pharmacy,
        Pharmacist $pharmacist,
    ): RedirectResponse {
        $validated = $request->validated();
        $oldDocument = $pharmacist->registration_document;
        $newDocument = $request->file('registration_document')?->store('pharmacies/pharmacists', 'local');

        DB::transaction(function () use ($pharmacy, $pharmacist, $validated, $oldDocument, $newDocument): void {
            if ($validated['is_primary'] ?? false) {
                $pharmacy->pharmacists()
                    ->where('id', '!=', $pharmacist->getKey())
                    ->update(['is_primary' => false]);
            }

            $pharmacist->update([
                ...$validated,
                'registration_document' => $newDocument ?? $oldDocument,
                'status'                => 'pending',
                'verified_at'           => null,
                'verified_by'           => null,
                'rejection_reason'      => null,
            ]);
        });

        if ($newDocument !== null) {
            Storage::disk('local')->delete($oldDocument);
        }

        return back()->with('success', 'Pharmacist updated and returned to review.');
    }

    public function destroy(Pharmacy $pharmacy, Pharmacist $pharmacist): RedirectResponse
    {
        $document = $pharmacist->registration_document;
        $pharmacist->delete();
        Storage::disk('local')->delete($document);

        return back()->with('success', 'Pharmacist deleted.');
    }

    public function download(Pharmacy $pharmacy, Pharmacist $pharmacist): StreamedResponse
    {
        abort_if(
            blank($pharmacist->registration_document)
                || ! Storage::disk('local')->exists($pharmacist->registration_document),
            404,
        );

        return Storage::disk('local')->download($pharmacist->registration_document);
    }

    public function review(Request $request, Pharmacy $pharmacy, Pharmacist $pharmacist): RedirectResponse
    {
        $validated = $request->validate([
            'status'           => ['required', 'in:verified,rejected'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:2000'],
        ]);

        $pharmacist->update([
            'status'           => $validated['status'],
            'verified_at'      => now(),
            'verified_by'      => $request->user()->id,
            'rejection_reason' => $validated['status'] === 'rejected'
                ? $validated['rejection_reason']
                : null,
        ]);

        return back()->with('success', 'Pharmacist review saved.');
    }
}
