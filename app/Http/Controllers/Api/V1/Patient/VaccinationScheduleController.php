<?php

namespace App\Http\Controllers\Api\V1\Patient;

use App\Http\Controllers\Controller;
use App\Models\VaccinationSchedule;
use App\Services\Vaccinations\VaccinationScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VaccinationScheduleController extends Controller
{
    public function __construct(
        private readonly VaccinationScheduleService $service
    ) {}

    /**
     * GET /api/v1/vaccination-schedules
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'vaccine_id' => [
                'sometimes',
                'integer',
                'exists:vaccines,id',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $schedules = $this->service->paginate($filters);

        return response()->json([
            'success' => true,
            'message' => 'Vaccination schedules retrieved successfully.',
            'data'    => $schedules,
        ]);
    }

    /**
     * POST /api/v1/vaccination-schedules
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vaccine_id' => [
                'required',
                'integer',
                Rule::exists('vaccines', 'id')
                    ->where('is_active', true),
            ],
            'schedule_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'dose_number' => [
                'required',
                'integer',
                'min:1',
                'max:255',
            ],
            'recommended_age_days' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'interval_days' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $schedule = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vaccination schedule created successfully.',
            'data'    => $schedule,
        ], 201);
    }

    /**
     * GET /api/v1/vaccination-schedules/{schedule}
     */
    public function show($id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Vaccination schedule retrieved successfully.',
            'data'    => $this->service->find($id),
        ]);
    }

    /**
     * PUT/PATCH /api/v1/vaccination-schedules/{schedule}
     */
    public function update(
        Request $request,
        VaccinationSchedule $schedule
    ): JsonResponse {
        $data = $request->validate([
            'vaccine_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('vaccines', 'id')
                    ->where('is_active', true),
            ],
            'schedule_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'dose_number' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:255',
            ],
            'recommended_age_days' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
            'interval_days' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ],
            'notes' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $updated = $this->service->update($schedule, $data);

        return response()->json([
            'success' => true,
            'message' => 'Vaccination schedule updated successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * DELETE /api/v1/vaccination-schedules/{schedule}
     */
    public function destroy($id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Vaccination schedule deleted successfully.',
            'data'    => null,
        ]);
    }
}
