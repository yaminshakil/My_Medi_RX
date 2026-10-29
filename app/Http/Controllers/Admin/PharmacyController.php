<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\SavePharmacyRequest;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search'   => ['nullable', 'string', 'max:100'],
            'status'   => ['nullable', Rule::in(['draft', 'pending', 'approved', 'rejected', 'suspended'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 15, 25, 50])],
        ]);

        $search = $validated['search'] ?? null;
        $status = $validated['status'] ?? null;
        $perPage = $validated['per_page'] ?? 15;

        $pharmacies = Pharmacy::query()
            ->with(['owner:id,name,email', 'address:id,pharmacy_id,division,district'])
            ->withCount(['licenses', 'pharmacists'])
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('owner', function ($ownerQuery) use ($search): void {
                            $ownerQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $statusCounts = Pharmacy::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('Admin/Pharmacies/Index', [
            'pharmacies' => $pharmacies,
            'filters'    => [
                'search'   => $search ?? '',
                'status'   => $status ?? '',
                'per_page' => $perPage,
            ],
            'stats' => [
                'total'     => $statusCounts->sum(),
                'pending'   => $statusCounts->get('pending', 0),
                'approved'  => $statusCounts->get('approved', 0),
                'rejected'  => $statusCounts->get('rejected', 0),
                'suspended' => $statusCounts->get('suspended', 0),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pharmacies/Create', [
            'owners' => $this->owners(),
        ]);
    }

    public function store(SavePharmacyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $logo = $request->file('logo')?->store('pharmacies/logos', 'public');

        $pharmacy = DB::transaction(function () use ($validated, $logo): Pharmacy {
            $pharmacy = Pharmacy::create([
                'user_id'     => $validated['user_id'],
                'name'        => $validated['name'],
                'slug'        => $this->uniqueSlug($validated['name']),
                'phone'       => $validated['phone'] ?? null,
                'email'       => $validated['email'] ?? null,
                'description' => $validated['description'] ?? null,
                'logo'        => $logo,
                'status'      => 'pending',
            ]);

            $this->saveAddress($pharmacy, $validated['address'] ?? []);

            return $pharmacy;
        });

        return to_route('admin.pharmacies.show', $pharmacy)
            ->with('success', 'Pharmacy created and queued for review.');
    }

    public function show(Pharmacy $pharmacy): Response
    {
        $pharmacy->load([
            'owner:id,name,email',
            'address',
            'licenses.verifier:id,name',
            'pharmacists.verifier:id,name',
            'verifier:id,name',
        ]);

        return Inertia::render('Admin/Pharmacies/Show', [
            'pharmacy' => $pharmacy,
        ]);
    }

    public function edit(Pharmacy $pharmacy): Response
    {
        $pharmacy->load(['owner:id,name,email', 'address']);

        return Inertia::render('Admin/Pharmacies/Edit', [
            'pharmacy' => $pharmacy,
            'owners'   => $this->owners($pharmacy->user_id),
        ]);
    }

    public function update(SavePharmacyRequest $request, Pharmacy $pharmacy): RedirectResponse
    {
        $validated = $request->validated();
        $oldLogo = $pharmacy->logo;
        $newLogo = $request->file('logo')?->store('pharmacies/logos', 'public');

        DB::transaction(function () use ($validated, $newLogo, $pharmacy): void {
            $pharmacy->update([
                'user_id'     => $validated['user_id'],
                'name'        => $validated['name'],
                'phone'       => $validated['phone'] ?? null,
                'email'       => $validated['email'] ?? null,
                'description' => $validated['description'] ?? null,
                'logo'        => $newLogo ?? $pharmacy->logo,
            ]);

            $this->saveAddress($pharmacy, $validated['address'] ?? []);
        });

        if ($newLogo !== null) {
            Storage::disk('public')->delete($oldLogo);
        }

        return to_route('admin.pharmacies.show', $pharmacy)
            ->with('success', 'Pharmacy details updated.');
    }

    public function destroy(Pharmacy $pharmacy): RedirectResponse
    {
        $logo = $pharmacy->logo;
        $licenseDocuments = $pharmacy->licenses()->pluck('document');
        $pharmacistDocuments = $pharmacy->pharmacists()->pluck('registration_document');

        DB::transaction(fn () => $pharmacy->delete());

        if ($logo !== null) {
            Storage::disk('public')->delete($logo);
        }
        Storage::disk('local')->delete($licenseDocuments->filter()->all());
        Storage::disk('local')->delete($pharmacistDocuments->filter()->all());

        return to_route('admin.pharmacies.index')->with('success', 'Pharmacy deleted.');
    }

    public function updateStatus(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $validated = $request->validate([
            'status'           => ['required', Rule::in(['approved', 'rejected', 'suspended'])],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === 'approved') {
            $hasReviewedLicense = $pharmacy->licenses()
                ->where('status', 'verified')
                ->where(function ($query): void {
                    $query->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', now()->toDateString());
                })
                ->exists();

            if (! $hasReviewedLicense) {
                return back()->withErrors([
                    'status' => 'Verify at least one current pharmacy license before approving this pharmacy.',
                ]);
            }

            if ($pharmacy->licenses()->where('status', 'pending')->exists()) {
                return back()->withErrors([
                    'status' => 'Review all pending pharmacy licenses before approving this pharmacy.',
                ]);
            }
        }

        $pharmacy->update([
            'status'           => $validated['status'],
            'rejection_reason' => $validated['status'] === 'rejected'
                ? $validated['rejection_reason']
                : null,
            'verified_by' => in_array($validated['status'], ['approved', 'rejected'], true)
                ? $request->user()->id
                : $pharmacy->verified_by,
            'verified_at' => in_array($validated['status'], ['approved', 'rejected'], true)
                ? now()
                : $pharmacy->verified_at,
        ]);

        return back()->with('success', 'Pharmacy status updated.');
    }

    /**
     * @return array<int, array{id: int, name: string, email: string|null}>
     */
    private function owners(?int $currentOwnerId = null): array
    {
        return User::query()
            ->where(function ($query) use ($currentOwnerId): void {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'Pharmacy'));

                if ($currentOwnerId !== null) {
                    $query->orWhereKey($currentOwnerId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function saveAddress(Pharmacy $pharmacy, array $address): void
    {
        $attributes = collect($address)
            ->only([
                'address_line_1',
                'address_line_2',
                'division',
                'district',
                'upazila',
                'union',
                'postal_code',
                'latitude',
                'longitude',
                'is_primary',
            ])
            ->all();

        if (filled($attributes['address_line_1'] ?? null)) {
            $pharmacy->address()->updateOrCreate([], $attributes + ['is_primary' => true]);
        } elseif ($pharmacy->address()->exists()) {
            $pharmacy->address()->delete();
        }
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 1;

        while (Pharmacy::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
