<?php

namespace App\Interfaces;

interface DashboardRepositoryInterface
{
    public function getToken();

    public function patientDashboardInfo(): array;

    // Eprescription
    public function ePrescriptionDashboardInfo();
}
