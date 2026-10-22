<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Pediatric\PediatricDoseCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PediatricDoseController extends Controller
{
    public function __construct(
        private readonly PediatricDoseCalculationService $doseService
    ) {
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicineId' => ['required', 'integer', 'exists:medicines,id'],
            'formulation' => ['required', 'string', 'max:150'],
            'indication' => ['required', 'string', 'max:150'],
            'childWeightKg' => ['required', 'numeric', 'gt:0', 'lte:300'],
            'childAgeMonths' => ['required', 'integer', 'min:0', 'max:2400'],
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        try {
            $result = $this->doseService->calculate(
                $validated,
                (int) $user->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Calculation completed using an approved rule.',
                'data' => $result,
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'A safe calculation could not be completed.',
                'errors' => $exception->errors(),
                'data' => null,
            ], 422);
        }
    }

    public function recent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $data = $this->doseService->getRecentCalculations(
            (int) $request->user()->id,
            (int) ($validated['limit'] ?? 10)
        );

        return response()->json([
            'success' => true,
            'message' => 'Recent calculations retrieved successfully.',
            'data' => $data,
        ]);
    }

    public function medicines(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Available pediatric dose rules retrieved successfully.',
            'data' => $this->doseService->getAvailableMedicines(),
        ]);
    }
}
