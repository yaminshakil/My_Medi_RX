<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return SubscriptionPlanResource::collection(SubscriptionPlan::latest()->paginate(20))
            ->additional(['success' => true]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $plan = SubscriptionPlan::create($data);
        return (new SubscriptionPlanResource($plan))->additional(['success' => true, 'message' => 'Plan created.'])->response()->setStatusCode(201);
    }

    public function show(SubscriptionPlan $plan)
    {
        return (new SubscriptionPlanResource($plan))->additional(['success' => true]);
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $this->validated($request, $plan);
        $data['slug'] = $data['slug'] ?? $plan->slug;
        $plan->update($data);
        return (new SubscriptionPlanResource($plan->fresh()))->additional(['success' => true, 'message' => 'Plan updated.']);
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            $plan->update(['is_active' => false]);
            return response()->json(['success' => true, 'message' => 'Plan has subscriptions, so it was deactivated instead of deleted.']);
        }
        $plan->delete();
        return response()->json(['success' => true, 'message' => 'Plan deleted.']);
    }

    private function validated(Request $request, ?SubscriptionPlan $plan = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('subscription_plans', 'slug')->ignore($plan?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'billing_cycle' => ['required', Rule::in(['monthly', 'quarterly', 'half_yearly', 'yearly', 'custom'])],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'user_type' => ['required', Rule::in(['All', 'Patient', 'Doctor', 'Pharmacy', 'Hospital'])],
            'features' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
