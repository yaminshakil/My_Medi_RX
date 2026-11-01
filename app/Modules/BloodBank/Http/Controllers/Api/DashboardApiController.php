<?php

namespace App\Modules\BloodBank\Http\Controllers\Api;

use App\Modules\BloodBank\Http\Resources\BloodBagResource;
use App\Modules\BloodBank\Http\Resources\BloodIssueResource;
use App\Modules\BloodBank\Models\BloodBag;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Repositories\Contracts\BloodIssueRepositoryInterface;
use App\Modules\BloodBank\Services\BloodStockService;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends BloodBankApiController
{
    public function __invoke(BloodStockService $stock, BloodIssueRepositoryInterface $issues): JsonResponse
    {
        return $this->success([
            'stock'    => $stock->overview(),
            'summary'  => $stock->summary(),
            'expiring' => $stock->expiringSoon(10)
                ->map(fn (BloodBag $bag) => BloodBagResource::make($bag)->resolve(request()))
                ->values(),
            'recent_issues' => $issues->recent(5)
                ->map(fn (BloodIssue $issue) => BloodIssueResource::make($issue)->resolve(request()))
                ->values(),
            'emergency_defaults' => [
                'radius_km'     => config('blood-bank.emergency.default_radius_km'),
                'max_radius_km' => config('blood-bank.emergency.max_radius_km'),
            ],
        ], 'Blood-bank dashboard loaded.');
    }
}
