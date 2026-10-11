<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Http\Resources\BloodBagResource;
use App\Modules\BloodBank\Http\Resources\BloodIssueResource;
use App\Modules\BloodBank\Repositories\Contracts\BloodIssueRepositoryInterface;
use App\Modules\BloodBank\Services\BloodStockService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(BloodStockService $stock, BloodIssueRepositoryInterface $issues): Response
    {
        // Closures keep each prop lazy, so the live-refresh partial reloads only run the queries they ask for.
        return Inertia::render('Admin/BloodBank/Dashboard', [
            'stock'    => fn () => $stock->overview(),
            'expiring' => fn () => $stock->expiringSoon(10)
                ->map(fn ($bag) => BloodBagResource::make($bag)->resolve())->values(),
            'summary'      => fn () => $stock->summary(),
            'recentIssues' => fn () => $issues->recent(5)
                ->map(fn ($issue) => BloodIssueResource::make($issue)->resolve())->values(),
            'emergencyDefaults' => [
                'radius_km' => config('blood-bank.emergency.default_radius_km'),
            ],
        ]);
    }
}
