<?php

namespace App\Services\Profile;

use App\Interfaces\Profile\ProfileRepositoryInterface;

class ProfileServices
{
    protected ProfileRepositoryInterface $profileRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(ProfileRepositoryInterface $profileRepository)
    {
        $this->profileRepository = $profileRepository;
    }

    public function getDoctorProfile()
    {
        return $this->profileRepository->getDoctorProfile();
    }

    public function createDoctorProfile(array $data)
    {
        return $this->profileRepository->createDoctorProfile($data);
    }

    public function updateDoctorProfile(array $data)
    {
        return $this->profileRepository->updateDoctorProfile($data);
    }

    public function getPatientProfile()
    {
        return $this->profileRepository->getPatientProfile();
    }
}
