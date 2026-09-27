<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Change user subscription plan
     */
    public function upgradePlan(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
        ]);

        // if ($validator->fails()) {
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->with('error', 'Please select a valid subscription plan.');
        // }

        try {
            $subscriptionPlan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

            // Check if user already has this plan active
            $currentSubscription = $user->subscription()
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            if ($currentSubscription && $currentSubscription->plan === $subscriptionPlan->plan_key) {
                $message = "User already has the {$subscriptionPlan->name} plan active.";

                return ApiResponseService::success($currentSubscription, $message);
            }

            // Deactivate existing active subscriptions
            $user->subscriptions()
                ->where('status', 'active')
                ->update(['status' => 'inactive', 'cancelled_at' => now()]);

            // Handle free plan differently
            if ($subscriptionPlan->plan_key === 'free') {
                Subscription::createFreeSubscription($user);
                $message = 'User successfully changed to Free plan!';
            } else {
                // Create new subscription based on selected plan
                $duration = $subscriptionPlan->duration_days ?? 365;

                // Enable all pro features for paid plans
                $features = [
                    'unlimited_quizzes'    => true,
                    'offline_access'       => true,
                    'sound_comparison'     => true,
                    'custom_quizzes'       => true,
                    'bookmarks'            => true,
                    'analytics'            => true,
                    'certificates'         => true,
                    'priority_support'     => true,
                    'ad_free'              => true,
                    'downloadable_content' => true,
                ];

                Subscription::create([
                    'user_id'           => $user->id,
                    'plan'              => $subscriptionPlan->plan_key,
                    'status'            => 'active',
                    'starts_at'         => now(),
                    'expires_at'        => now()->addDays($duration),
                    'features'          => $features,
                    'amount'            => $subscriptionPlan->unit_amount ? ($subscriptionPlan->unit_amount / 100) : 0,
                    'currency'          => $subscriptionPlan->currency ?? 'USD',
                    'payment_status'    => 'completed',
                    'payment_method'    => 'admin_change',
                    'notes'             => 'Plan changed by admin from user edit page',
                    'stripe_price_id'   => $subscriptionPlan->stripe_price_id,
                    'stripe_product_id' => $subscriptionPlan->stripe_product_id,
                ]);

                $message = "User successfully changed to {$subscriptionPlan->name} plan!";
            }

            // Update user account type based on subscription
            $user->syncAccountTypeFromSubscriptions();

            return ApiResponseService::success([], $message);
        } catch (\Exception $e) {
            \Log::error('User plan change failed: '.$e->getMessage(), [
                'user_id' => $user->id,
                'plan_id' => $request->subscription_plan_id,
                'error'   => $e->getMessage(),
            ]);

            return ApiResponseService::error([], 'Failed to change user plan: '.$e->getMessage());
        }
    }
}
