<?php

namespace App\Http\Controllers\Api\V1\Pregnancy;

use App\Http\Controllers\Controller;
use App\Models\PregnancyProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PregnancyProfileController extends Controller
{
    /**
     * Get the authenticated user's pregnancy profile.
     */
    public function show(Request $request)
    {
        $profile = PregnancyProfile::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'message' => 'Pregnancy profile not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Pregnancy profile retrieved successfully.',
            'data' => [
            'id' => $profile->id,
            'userId' => $profile->user_id,
            'lastMenstrualPeriod' => $profile->last_menstrual_period?->format('Y-m-d'),
            'dueDate' => $profile->due_date?->format('Y-m-d'),
            'datingConfirmedDate' => $profile->dating_confirmed_date?->format('Y-m-d'),
            'isActive' => (bool) $profile->is_active,
            'createdAt' => $profile->created_at?->toISOString(),
            'updatedAt' => $profile->updated_at?->toISOString(),
        ],
        ]);
    }

    /**
     * Create or update the authenticated user's pregnancy profile.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'last_menstrual_period' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'dating_confirmed_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
        ]);

        $lmp = Carbon::parse(
            $validated['last_menstrual_period']
        )->startOfDay();

        // Estimated due date = LMP + 280 days.
        $dueDate = $lmp->copy()->addDays(280);

        $profile = PregnancyProfile::updateOrCreate(
            [
                'user_id' => $request->user()->id,
            ],
            [
                'last_menstrual_period' => $lmp->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'dating_confirmed_date' =>
                    $validated['dating_confirmed_date'] ?? null,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'Pregnancy profile saved successfully.',
            'data' => [
                'id' => $profile->id,
                'lastMenstrualPeriod' =>
                    $profile->last_menstrual_period?->toDateString(),
                'dueDate' => $profile->due_date?->toDateString(),
                'datingConfirmedDate' =>
                    $profile->dating_confirmed_date?->toDateString(),
                'isActive' => $profile->is_active,
            ],
        ], 201);
    }

    /**
     * Stop tracking the current pregnancy.
     */
    public function deactivate(Request $request)
    {
        $profile = PregnancyProfile::where(
            'user_id',
            $request->user()->id
        )->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'statusCode' => 404,
                'message' => 'Pregnancy profile not found.',
                'data' => null,
            ], 404);
        }

        $profile->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Pregnancy tracking deactivated.',
            'data' => null,
        ]);
    }
}
