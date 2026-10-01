<?php

namespace App\Repositories\Eprescriptions;

use App\Interfaces\Eprescriptions\GynaeHistoryRepositoryInterface;
use App\Models\GynaeHistory;

class GynaeHistoryRepository implements GynaeHistoryRepositoryInterface
{
    public function getByPrescriptionId(int $prescriptionId): ?GynaeHistory
    {
        return GynaeHistory::where('prescription_id', $prescriptionId)->first();
    }

    public function create(array $data): GynaeHistory
    {
        return GynaeHistory::create($data);
    }

    public function update(GynaeHistory $history, array $data): GynaeHistory
    {
        $history->update($data);

        return $history;
    }

    public function delete(GynaeHistory $history): bool
    {
        return $history->delete();
    }
}
