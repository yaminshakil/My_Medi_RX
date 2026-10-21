<?php

namespace App\Services\Vaccinations;

use App\Models\VaccinationSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VaccinationScheduleService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return VaccinationSchedule::query()
            ->with('vaccine')
            ->when(
                isset($filters['vaccine_id']),
                fn (Builder $query) => $query->where(
                    'vaccine_id',
                    $filters['vaccine_id']
                )
            )
            ->when(
                array_key_exists('is_active', $filters),
                fn (Builder $query) => $query->where(
                    'is_active',
                    $filters['is_active']
                ),
                fn (Builder $query) => $query->where('is_active', true)
            )
            ->whereHas('vaccine', function (Builder $query) {
                $query->where('is_active', true);
            })
            ->orderBy('vaccine_id')
            ->orderBy('dose_number')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();
    }

    public function find(int $id): VaccinationSchedule
    {
        return VaccinationSchedule::query()
            ->with('vaccine', 'vaccine.manufacturer')
            ->findOrFail($id);
    }

    public function create(array $data): VaccinationSchedule
    {
        return DB::transaction(function () use ($data) {
            $this->ensureUniqueDose($data);

            $schedule = VaccinationSchedule::create($data);

            return $schedule->load('vaccine', 'vaccine.manufacturer');
        });
    }

    public function update(
        VaccinationSchedule $schedule,
        array $data
    ): VaccinationSchedule {
        return DB::transaction(function () use ($schedule, $data) {
            $this->ensureUniqueDose(
                array_merge($schedule->getAttributes(), $data),
                $schedule->id
            );

            $schedule->update($data);

            return $schedule->fresh()->load('vaccine', 'vaccine.manufacturer');
        });
    }

    public function delete($id): void
    {
        $schedule = VaccinationSchedule::findOrFail($id);
        if ($schedule->records()->exists()) {
            throw ValidationException::withMessages([
                'schedule' => [
                    'This schedule has vaccination records. Deactivate it instead of deleting it.',
                ],
            ]);
        }

        $schedule->delete();
    }

    private function ensureUniqueDose(
        array $data,
        ?int $ignoreId = null
    ): void {
        $query = VaccinationSchedule::query()
            ->where('vaccine_id', $data['vaccine_id'])
            ->where('dose_number', $data['dose_number']);

        if (! empty($data['schedule_name'])) {
            $query->where('schedule_name', $data['schedule_name']);
        } else {
            $query->whereNull('schedule_name');
        }

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'dose_number' => [
                    'This dose already exists for this vaccine and schedule name.',
                ],
            ]);
        }
    }
}
