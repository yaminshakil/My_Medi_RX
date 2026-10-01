<?php

namespace App\Interfaces\Eprescriptions;

use App\Models\GynaeHistory;

interface GynaeHistoryRepositoryInterface
{
    public function getByPrescriptionId(int $prescriptionId): ?GynaeHistory;

    public function create(array $data): GynaeHistory;

    public function update(GynaeHistory $history, array $data): GynaeHistory;

    public function delete(GynaeHistory $history): bool;
}
