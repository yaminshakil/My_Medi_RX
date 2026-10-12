<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Http\Requests\StoreDonorRequest;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Repositories\Contracts\DonorRepositoryInterface;
use App\Modules\BloodBank\Support\LookupOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DonorController extends Controller
{
    public function __construct(private readonly DonorRepositoryInterface $donors)
    {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'blood_group', 'verified']);

        return Inertia::render('Admin/BloodBank/Donors/Index', [
            'donors' => $this->donors->paginate($filters)->through(fn ($d) => [
                'id'              => $d->id,
                'name'            => $d->name,
                'phone'           => $d->phone,
                'email'           => $d->email,
                'blood_group'     => $d->blood_group->value,
                'city'            => $d->city,
                'last_donated_at' => $d->last_donated_at?->toDateString(),
                'is_verified'     => $d->is_verified,
            ]),
            'filters' => (object) $filters,
            'groups'  => LookupOptions::groups(),
        ]);
    }

    public function store(StoreDonorRequest $request): RedirectResponse
    {
        $this->donors->create($request->validated());

        return back()->with('success', 'Donor added. Verify them before they can receive emergency alerts.');
    }

    public function verify(Request $request, BloodDonor $donor): RedirectResponse
    {
        $data = $request->validate(['verified' => ['required', 'boolean']]);

        $this->donors->setVerified($donor, $data['verified']);

        return back()->with('success', $data['verified'] ? 'Donor verified.' : 'Verification removed.');
    }
}
