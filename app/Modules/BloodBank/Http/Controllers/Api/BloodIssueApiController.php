<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Modules\BloodBank\Enums\BloodComponent;
use App\Modules\BloodBank\Exceptions\BloodBankException;
use App\Modules\BloodBank\Http\Requests\StoreBloodIssueRequest;
use App\Modules\BloodBank\Http\Resources\BloodIssueResource;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Repositories\Contracts\BloodIssueRepositoryInterface;
use App\Modules\BloodBank\Repositories\Contracts\PatientRepositoryInterface;
use App\Modules\BloodBank\Services\BloodIssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodIssueApiController extends BloodBankApiController
{
    public function __construct(
        private readonly BloodIssueRepositoryInterface $issues,
        private readonly PatientRepositoryInterface $patients,
        private readonly BloodIssueService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search'       => ['nullable', 'string', 'max:100'],
            'component'    => ['nullable', Rule::in(BloodComponent::values())],
            'physician_id' => ['nullable', 'integer', 'exists:bb_physicians,id'],
            'from'         => ['nullable', 'date'],
            'to'           => ['nullable', 'date', 'after_or_equal:from'],
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $perPage = $filters['per_page'] ?? 20;
        unset($filters['per_page']);

        return $this->paginated(
            $this->issues->paginate($filters, $perPage),
            fn (BloodIssue $issue): array => BloodIssueResource::make($issue)->resolve($request),
        );
    }

    public function availableBags(Request $request): JsonResponse
    {
        $data = $request->validate([
            'patient_id'  => ['nullable', 'integer', 'exists:bb_patients,id'],
            'component'   => ['required', Rule::in(BloodComponent::values())],
            'include_all' => ['nullable', 'boolean'],
        ]);
        $patient = isset($data['patient_id']) ? $this->patients->find($data['patient_id']) : null;

        return $this->success(
            $this->service->availableBagsFor(
                $patient,
                BloodComponent::from($data['component']),
                $request->boolean('include_all'),
            ),
            'Available blood units loaded.',
        );
    }

    public function store(StoreBloodIssueRequest $request): JsonResponse
    {
        try {
            $issue = $this->service->issue($request->user(), $request->validated());
        } catch (BloodBankException $exception) {
            return $this->conflict($exception);
        }

        $issue->load(['bag.storageUnit', 'patient', 'physician', 'issuer:id,name', 'hospital:id,hospital_name']);

        return $this->success(BloodIssueResource::make($issue)->resolve($request), 'Blood unit issued and logged.', 201);
    }

    public function show(Request $request, BloodIssue $issue): JsonResponse
    {
        $issue->load(['bag.storageUnit', 'patient', 'physician', 'issuer:id,name', 'hospital:id,hospital_name']);

        return $this->success(BloodIssueResource::make($issue)->resolve($request), 'Blood issue loaded.');
    }

    public function outcome(Request $request, BloodIssue $issue): JsonResponse
    {
        $data = $request->validate([
            'transfusion_started_at'   => ['nullable', 'date'],
            'transfusion_completed_at' => ['nullable', 'date', 'after_or_equal:transfusion_started_at'],
            'reaction_noted'           => ['boolean'],
            'reaction_notes'           => ['nullable', 'required_if:reaction_noted,true', 'string', 'max:2000'],
        ]);

        $this->service->recordOutcome($issue, $data);
        $issue->load(['bag.storageUnit', 'patient', 'physician', 'issuer:id,name', 'hospital:id,hospital_name']);

        return $this->success(BloodIssueResource::make($issue)->resolve($request), 'Transfusion outcome saved.');
    }
}
