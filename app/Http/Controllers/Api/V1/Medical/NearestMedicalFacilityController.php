<?php

namespace App\Http\Controllers\Api\V1\Medical;

use App\Http\Controllers\Controller;
use App\Http\Resources\Medical\MedicalFacilityResource;
use App\Services\Hospital\NearestMedicalFacilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NearestMedicalFacilityController extends Controller
{
    public function __construct(
        private readonly NearestMedicalFacilityService $facilityService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->query(), [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['sometimes', 'numeric', 'gt:0', 'max:100'],
            'query' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => 'Invalid facility search parameters.',
                'errors' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        $validated = $validator->validated();

        $facilities = $this->facilityService->search(
            latitude: (float) $validated['lat'],
            longitude: (float) $validated['lng'],
            radius: (float) ($validated['radius'] ?? 5),
            query: $validated['query'] ?? null,
        );

        $data = MedicalFacilityResource::collection($facilities)
            ->resolve($request);

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'Medical facilities retrieved',
            'data' => $data,
        ]);
    }
}
