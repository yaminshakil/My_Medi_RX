<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()?->getRoleNames()->first() ?? 'Patient';
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->whereIn('user_type', ['All', $role])
            ->orderBy('price')
            ->get();

        return SubscriptionPlanResource::collection($plans)->additional([
            'success' => true,
            'message' => 'Subscription plans retrieved successfully.',
        ]);
    }

    public function show(SubscriptionPlan $plan)
    {
        abort_unless($plan->is_active, 404);

        return (new SubscriptionPlanResource($plan))->additional(['success' => true]);
    }
}
