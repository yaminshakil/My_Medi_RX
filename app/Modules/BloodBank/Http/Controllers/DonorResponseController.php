<?php

namespace App\Modules\BloodBank\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\EmergencyRequest;
use App\Modules\BloodBank\Services\EmergencyBroadcastService;
use Inertia\Inertia;
use Inertia\Response;

/** Landing page for the accept / decline links in the donor email. The `signed` middleware has already checked the URL. */
class DonorResponseController extends Controller
{
    public function __invoke(
        EmergencyBroadcastService $service,
        EmergencyRequest $emergencyRequest,
        BloodDonor $donor,
        string $response
    ): Response {
        $recorded = $service->recordDonorResponse($emergencyRequest, $donor, $response);

        return Inertia::render('BloodBank/Public/DonorResponse', [
            'recorded' => $recorded,
            'response' => $response,
            'hospital' => $emergencyRequest->hospital?->name,
            'donor'    => $donor->name,
        ]);
    }
}
