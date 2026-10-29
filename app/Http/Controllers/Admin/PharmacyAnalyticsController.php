<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\PharmacyAnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyAnalyticsController extends Controller
{
    public function __invoke(PharmacyAnalyticsService $analytics): Response
    {
        return Inertia::render('Admin/Pharmacies/Analytics', $analytics->dashboard());
    }
}
