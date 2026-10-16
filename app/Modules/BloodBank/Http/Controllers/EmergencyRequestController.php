<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Enums\EmergencyStatus;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Http\Requests\StoreEmergencyRequestRequest;
use App\Modules\BloodBank\Http\Resources\EmergencyRequestResource;
use App\Modules\BloodBank\Models\EmergencyRequest;
use App\Modules\BloodBank\Repositories\Contracts\EmergencyRequestRepositoryInterface;
use App\Modules\BloodBank\Services\EmergencyBroadcastService;
use App\Modules\BloodBank\Support\LookupOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmergencyRequestController extends Controller
{
    public function __construct(
        private readonly EmergencyRequestRepositoryInterface $requests,
        private readonly EmergencyBroadcastService $service,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/BloodBank/Emergency/Index', [
            'requests' => $this->requests->paginate($request->only('status'))
                ->through(fn ($r) => EmergencyRequestResource::make($r)->resolve()),
            'options' => [
                'groups'     => LookupOptions::groups(),
                'components' => LookupOptions::components(),
                'urgencies'  => LookupOptions::urgencies(),
            ],
            'defaults' => [
                'radius_km'     => config('blood-bank.emergency.default_radius_km'),
                'max_radius_km' => config('blood-bank.emergency.max_radius_km'),
            ],
        ]);
    }

    public function store(StoreEmergencyRequestRequest $request): RedirectResponse
    {
        try {
            $emergency = $this->service->broadcast($request->user(), $request->validated());
        } catch (BloodBankException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // Zero matches is not a failure of the system, but staff must know nobody was alerted.
        if ($emergency->donors_notified_count === 0) {
            return redirect()->route('blood-bank.emergency.show', $emergency)
                ->with('error', "No eligible verified donors within {$emergency->radius_km} km. Widen the radius and send again.");
        }

        return redirect()->route('blood-bank.emergency.show', $emergency)
            ->with('success', "Request sent to {$emergency->donors_notified_count} donor(s).");
    }

    public function show(EmergencyRequest $emergencyRequest): Response
    {
        $emergencyRequest->load(['hospital:id,name', 'donors']);

        return Inertia::render('Admin/BloodBank/Emergency/Show', [
            'request' => EmergencyRequestResource::make($emergencyRequest)->resolve(),
            'donors'  => $emergencyRequest->donors
                ->sortBy(fn ($d) => $d->pivot->distance_km)
                ->map(fn ($d) => [
                    'id'           => $d->id,
                    'name'         => $d->name,
                    'phone'        => $d->phone,
                    'blood_group'  => $d->blood_group->value,
                    'distance_km'  => (float) $d->pivot->distance_km,
                    'response'     => $d->pivot->response,
                    'responded_at' => $d->pivot->responded_at,
                ])->values(),
        ]);
    }

    public function fulfill(EmergencyRequest $emergencyRequest): RedirectResponse
    {
        return $this->close($emergencyRequest, EmergencyStatus::Fulfilled, 'Request marked as fulfilled.');
    }

    public function cancel(EmergencyRequest $emergencyRequest): RedirectResponse
    {
        return $this->close($emergencyRequest, EmergencyStatus::Cancelled, 'Request cancelled.');
    }

    private function close(EmergencyRequest $emergencyRequest, EmergencyStatus $status, string $message): RedirectResponse
    {
        try {
            $this->service->close($emergencyRequest, $status);
        } catch (BloodBankException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }
}
