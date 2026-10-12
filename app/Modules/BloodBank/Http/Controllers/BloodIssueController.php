<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Http\Requests\StoreBloodIssueRequest;
use App\Modules\BloodBank\Http\Resources\BloodIssueResource;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Repositories\Contracts\BloodIssueRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\PatientRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\PhysicianRepositoryInterface;
use App\Modules\BloodBank\Services\BloodIssueService;
use App\Modules\BloodBank\Support\LookupOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodIssueController extends Controller
{
    public function __construct(
        private readonly BloodIssueRepositoryInterface $issues,
        private readonly PatientRepositoryInterface $patients,
        private readonly PhysicianRepositoryInterface $physicians,
        private readonly BloodIssueService $service,
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'component', 'physician_id', 'from', 'to']);

        return Inertia::render('Admin/BloodBank/Issues/Index', [
            'issues'     => $this->issues->paginate($filters)->through(fn ($i) => BloodIssueResource::make($i)->resolve()),
            'filters'    => (object) $filters,
            'components' => LookupOptions::components(),
            'physicians' => $this->physicians->active()->map(fn ($p) => ['value' => $p->id, 'label' => $p->name])->values(),
        ]);
    }

    /**
     * The form is driven by query-string partial reloads: search a patient, pick a component,
     * and the server returns matching units (first-expiry-first-out).
     */
    public function create(Request $request): Response
    {
        $patient   = $request->filled('patient_id') ? $this->patients->find((int) $request->input('patient_id')) : null;
        $component = BloodComponent::tryFrom((string) $request->input('component'));

        return Inertia::render('Admin/BloodBank/Issues/Create', [
            'filters'  => (object) $request->only(['patient_search', 'patient_id', 'component', 'include_all']),
            'patients' => fn () => $request->filled('patient_search')
                ? $this->patients->search($request->input('patient_search'))->map(fn ($p) => $this->patientRow($p))->values()
                : [],
            'patient'       => $patient ? $this->patientRow($patient) : null,
            'availableBags' => fn () => $component
                ? $this->service->availableBagsFor($patient, $component, $request->boolean('include_all'))
                : [],
            'physicians' => $this->physicians->active()->map(fn ($p) => [
                'value' => $p->id,
                'label' => $p->name.($p->department ? " ({$p->department})" : ''),
            ])->values(),
            'components' => LookupOptions::components(),
        ]);
    }

    public function store(StoreBloodIssueRequest $request): RedirectResponse
    {
        try {
            $issue = $this->service->issue($request->user(), $request->validated());
        } catch (BloodBankException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('blood-bank.issues.show', $issue)->with('success', 'Unit issued and logged.');
    }

    public function show(BloodIssue $issue): Response
    {
        $issue->load(['bag.storageUnit', 'patient', 'physician', 'issuer:id,name']);

        return Inertia::render('Admin/BloodBank/Issues/Show', [
            'issue' => BloodIssueResource::make($issue)->resolve(),
        ]);
    }

    public function outcome(Request $request, BloodIssue $issue): RedirectResponse
    {
        $data = $request->validate([
            'transfusion_started_at'   => ['nullable', 'date'],
            'transfusion_completed_at' => ['nullable', 'date', 'after_or_equal:transfusion_started_at'],
            'reaction_noted'           => ['boolean'],
            'reaction_notes'           => ['nullable', 'required_if:reaction_noted,true', 'string', 'max:2000'],
        ]);

        $this->service->recordOutcome($issue, $data);

        return back()->with('success', 'Transfusion outcome saved.');
    }

    private function patientRow($p): array
    {
        return [
            'id'               => $p->id,
            'name'             => $p->name,
            'mrn'              => $p->mrn,
            'clinical_file_no' => $p->clinical_file_no,
            'blood_group'      => $p->blood_group?->value,
        ];
    }
}
