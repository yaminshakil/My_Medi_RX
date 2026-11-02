<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Http\Requests\StorePhysicianRequest;
use App\Modules\BloodBank\Models\Physician;
use App\Modules\BloodBank\Repositories\Contracts\PhysicianRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhysicianController extends Controller
{
    public function __construct(private readonly PhysicianRepositoryInterface $physicians) {}

    public function index(Request $request): Response
    {
        $filters = $request->only('search');

        return Inertia::render('Admin/BloodBank/Physicians/Index', [
            'physicians' => $this->physicians->paginate($filters)->through(fn ($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'license_no' => $p->license_no,
                'department' => $p->department,
                'phone'      => $p->phone,
                'is_active'  => $p->is_active,
            ]),
            'filters' => (object) $filters,
        ]);
    }

    public function store(StorePhysicianRequest $request): RedirectResponse
    {
        $this->physicians->create($request->validated());

        return back()->with('success', 'Physician added.');
    }

    // Deactivated physicians disappear from the issue form but stay on past issue records.
    public function toggle(Physician $physician): RedirectResponse
    {
        $this->physicians->setActive($physician, ! $physician->is_active);

        return back()->with('success', $physician->is_active ? 'Physician activated.' : 'Physician deactivated.');
    }
}
