<?php

namespace App\Modules\BloodBank\Repositories\Eloquent;

use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Repositories\Contracts\BloodIssueRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BloodIssueRepository implements BloodIssueRepositoryInterface
{
    // Eager loads shared by the list, the dashboard and the detail page.
    private const WITH = ['bag.storageUnit', 'patient', 'physician', 'issuer:id,name'];

    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return BloodIssue::query()
            ->with(self::WITH)
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('issue_number', 'like', "%{$s}%")
                ->orWhereHas('patient', fn ($p) => $p
                    ->where('name', 'like', "%{$s}%")
                    ->orWhere('mrn', 'like', "%{$s}%")
                    ->orWhere('clinical_file_no', 'like', "%{$s}%"))
                ->orWhereHas('bag', fn ($b) => $b->where('bag_number', 'like', "%{$s}%"))))
            ->when($filters['component'] ?? null, fn ($q, $v) => $q->whereHas('bag', fn ($b) => $b->where('component', $v)))
            ->when($filters['physician_id'] ?? null, fn ($q, $v) => $q->where('physician_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->where('issued_at', '>=', $v.' 00:00:00'))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->where('issued_at', '<=', $v.' 23:59:59'))
            ->latest('issued_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): BloodIssue
    {
        return BloodIssue::create($data);
    }

    public function update(BloodIssue $issue, array $data): BloodIssue
    {
        $issue->update($data);

        return $issue;
    }

    public function recent(int $limit = 5): Collection
    {
        return BloodIssue::query()->with(self::WITH)->latest('issued_at')->limit($limit)->get();
    }

    public function countSince(CarbonInterface $since): int
    {
        return BloodIssue::query()->where('issued_at', '>=', $since)->count();
    }
}
