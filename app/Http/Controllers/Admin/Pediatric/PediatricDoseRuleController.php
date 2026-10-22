<?php

namespace App\Http\Controllers\Admin\Pediatric;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\PediatricDoseRule;
use App\Services\Pediatric\PediatricDoseRuleWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PediatricDoseRuleController extends Controller
{
    public function __construct(
        private readonly PediatricDoseRuleWorkflowService $workflow
    ) {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PediatricDoseRule::class);

        $query = PediatricDoseRule::query()
            ->with(['medicine', 'creator', 'reviewer'])
            ->latest();

        if (!$request->user()->can('pediatric-dose-rules.view-all')) {
            $query->where('created_by', $request->user()->id);
        }

        if ($request->filled('status')) {
            $request->validate([
                'status' => [
                    'nullable',
                    Rule::in([
                        'draft',
                        'pending_review',
                        'approved',
                        'rejected',
                    ]),
                ],
            ]);

            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder->where('formulation', 'like', "%{$search}%")
                    ->orWhere('indication', 'like', "%{$search}%")
                    ->orWhere('source_reference', 'like', "%{$search}%")
                    ->orWhereHas('medicine', function ($medicineQuery) use ($search) {
                        $medicineQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $rules = $query->paginate(15)->withQueryString();

        $base = PediatricDoseRule::query();

        if (!$request->user()->can('pediatric-dose-rules.view-all')) {
            $base->where('created_by', $request->user()->id);
        }

        return Inertia::render('Admin/PediatricDoseRules/Index', [
            'rules' => $rules,
            'filters' => $request->only(['status', 'search']),
            'stats' => [
                'total' => (clone $base)->count(),
                'draft' => (clone $base)->where('status', 'draft')->count(),
                'pending' => (clone $base)->where('status', 'pending_review')->count(),
                'approved' => (clone $base)->where('status', 'approved')->count(),
                'rejected' => (clone $base)->where('status', 'rejected')->count(),
            ],
            'permissions' => [
                'create' => $request->user()->can('pediatric-dose-rules.create'),
                'viewAll' => $request->user()->can('pediatric-dose-rules.view-all'),
                'approve' => $request->user()->can('pediatric-dose-rules.approve'),
                'activate' => $request->user()->can('pediatric-dose-rules.activate'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', PediatricDoseRule::class);

        return Inertia::render('Admin/PediatricDoseRules/Create', [
            'medicines' => Medicine::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($medicine) => [
                    'value' => (string) $medicine->id,
                    'label' => $medicine->name,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PediatricDoseRule::class);

        $data = $this->validatedRule($request);

        DB::transaction(function () use ($request, $data) {
            $rule = PediatricDoseRule::create([
                ...$data,
                'created_by' => $request->user()->id,
                'status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
                'is_active' => false,
                'version' => 1,
            ]);

            $this->workflow->recordChange(
                $rule,
                (int) $request->user()->id,
                ['created' => $data],
                $request->ip()
            );
        });

        return to_route('admin.pediatric-dose-rules.index')
            ->with('success', 'Draft pediatric dose rule created.');
    }

    public function show(PediatricDoseRule $pediatricDoseRule): Response
    {
        $this->authorize('view', $pediatricDoseRule);

        $pediatricDoseRule->load(['medicine', 'creator', 'reviewer', 'approver']);

        return Inertia::render('Admin/PediatricDoseRules/Show', [
            'rule' => $pediatricDoseRule,
            'audits' => $pediatricDoseRule->audits()
                ->with('user:id,name')
                ->paginate(20),
        ]);
    }

    public function edit(PediatricDoseRule $pediatricDoseRule): Response
    {
        $this->authorize('update', $pediatricDoseRule);

        return Inertia::render('Admin/PediatricDoseRules/Edit', [
            'rule' => $pediatricDoseRule,
            'medicines' => Medicine::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($medicine) => [
                    'value' => (string) $medicine->id,
                    'label' => $medicine->name,
                ]),
        ]);
    }

    public function update(
        Request $request,
        PediatricDoseRule $pediatricDoseRule
    ): RedirectResponse {
        $this->authorize('update', $pediatricDoseRule);

        $data = $this->validatedRule($request);
        $before = $pediatricDoseRule->only(array_keys($data));

        DB::transaction(function () use (
            $request,
            $pediatricDoseRule,
            $data,
            $before
        ) {
            $pediatricDoseRule->update($data);

            $this->workflow->recordChange(
                $pediatricDoseRule,
                (int) $request->user()->id,
                ['before' => $before, 'after' => $data],
                $request->ip()
            );
        });

        return to_route('admin.pediatric-dose-rules.show', $pediatricDoseRule)
            ->with('success', 'Draft rule updated.');
    }

    public function submit(
        Request $request,
        PediatricDoseRule $pediatricDoseRule
    ): RedirectResponse {
        $this->authorize('submit', $pediatricDoseRule);

        $this->workflow->submit(
            $pediatricDoseRule,
            (int) $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Rule submitted for clinical review.');
    }

    public function approve(
        Request $request,
        PediatricDoseRule $pediatricDoseRule
    ): RedirectResponse {
        $this->authorize('approve', $pediatricDoseRule);

        $this->workflow->approve(
            $pediatricDoseRule,
            (int) $request->user()->id,
            $request->ip()
        );

        return back()->with(
            'success',
            'Rule approved. It is not active until separately activated.'
        );
    }

    public function reject(
        Request $request,
        PediatricDoseRule $pediatricDoseRule
    ): RedirectResponse {
        $this->authorize('reject', $pediatricDoseRule);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $this->workflow->reject(
            $pediatricDoseRule,
            (int) $request->user()->id,
            $data['reason'],
            $request->ip()
        );

        return back()->with('success', 'Rule rejected with a recorded reason.');
    }

    public function activate(
        Request $request,
        PediatricDoseRule $pediatricDoseRule
    ): RedirectResponse {
        $this->authorize('activate', $pediatricDoseRule);

        $this->workflow->activate(
            $pediatricDoseRule,
            (int) $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Approved rule activated.');
    }

    public function deactivate(
        Request $request,
        PediatricDoseRule $pediatricDoseRule
    ): RedirectResponse {
        $this->authorize('deactivate', $pediatricDoseRule);

        $this->workflow->deactivate(
            $pediatricDoseRule,
            (int) $request->user()->id,
            $request->ip()
        );

        return back()->with('success', 'Rule deactivated.');
    }

    private function validatedRule(Request $request): array
    {
        return $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'formulation' => ['required', 'string', 'max:150'],
            'indication' => ['required', 'string', 'max:150'],
            'concentration_mg' => ['required', 'numeric', 'gt:0'],
            'concentration_ml' => ['required', 'numeric', 'gt:0'],
            'dose_mg_per_kg_per_dose' => ['required', 'numeric', 'gt:0'],
            'max_single_dose_mg' => ['nullable', 'numeric', 'gt:0'],
            'max_daily_mg_per_kg' => ['nullable', 'numeric', 'gt:0'],
            'max_daily_dose_mg' => ['nullable', 'numeric', 'gt:0'],
            'dose_interval_hours' => ['nullable', 'integer', 'min:1'],
            'max_doses_per_24_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'min_age_months' => ['required', 'integer', 'min:0'],
            'max_age_months' => ['nullable', 'integer', 'gte:min_age_months'],
            'min_weight_kg' => ['nullable', 'numeric', 'gt:0'],
            'max_weight_kg' => ['nullable', 'numeric', 'gt:min_weight_kg'],
            'route' => ['required', 'string', 'max:50'],
            'contraindications' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'source_reference' => ['required', 'string', 'max:1000'],
            'source_version' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
