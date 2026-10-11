<?php

namespace App\Modules\BloodBank\Repositories\Contracts;

use App\Modules\BloodBank\Models\BloodIssue;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BloodIssueRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): BloodIssue;

    public function update(BloodIssue $issue, array $data): BloodIssue;

    public function recent(int $limit = 5): Collection;

    public function countSince(CarbonInterface $since): int;
}
