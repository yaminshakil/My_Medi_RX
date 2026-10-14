<?php

namespace App\Interfaces\Profile;

interface ProfileRepositoryInterface
{
    public function getDoctorProfile($id);

    public function createDoctorProfile(array $data);

    public function updateDoctorProfile(array $data);

    public function getPatientProfile();
}
