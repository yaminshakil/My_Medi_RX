<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request): JsonResponse|Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'data' => SubscriptionPlan::query()->latest()->get(),
            ]);
        }

        return Inertia::render('Admin/subscriptions/Plans/Index');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['slug'] = $data['slug'] ?? null;

        if (blank($data['slug'])) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        }

        $data['is_active'] = $data['is_active'] ?? true;
        SubscriptionPlan::create($data);

        return $this->respond($request, 'Subscription plan created.', 201);
    }

    public function update(Request $request, SubscriptionPlan $plan): JsonResponse|RedirectResponse
    {
        $data = $request->validate($this->rules($plan));
        $plan->update($data);

        return $this->respond($request, 'Subscription plan updated.');
    }

    public function destroy(Request $request, SubscriptionPlan $plan): JsonResponse|RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            $plan->update(['is_active' => false]);

            return $this->respond($request, 'Plan deactivated because it has subscription history.');
        }

        $plan->delete();

        return $this->respond($request, 'Subscription plan deleted.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?SubscriptionPlan $plan = null): array
    {
        $slugRule = Rule::unique('subscription_plans', 'slug');

        if ($plan !== null) {
            $slugRule->ignore($plan);
        }

        return [
            'name'          => ['required', 'string', 'max:120'],
            'slug'          => ['sometimes', 'nullable', 'string', 'max:255', $slugRule],
            'description'   => ['nullable', 'string'],
            'price'         => ['required', 'numeric', 'min:0', 'max:500000'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'quarterly', 'half_yearly', 'yearly', 'custom'])],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'user_type'     => ['required', Rule::in(['All', 'Patient', 'Doctor', 'Pharmacy', 'Hospital'])],
            'features'      => ['nullable', 'array'],
            'is_active'     => [$plan === null ? 'sometimes' : 'required', 'boolean'],
        ];
    }

    private function respond(Request $request, string $message, int $status = 200): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('success', $message);
    }
}
