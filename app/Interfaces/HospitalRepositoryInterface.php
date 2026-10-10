<?php

namespace App\Interfaces;

interface HospitalRepositoryInterface
{
    public function getAllHospital();
    public function getHospital();
    public function getHospitalById($id);
    public function createHospital($data);
    public function updateHospital($data, $id);
    public function deleteHospital($id);
    public function getAllHospitalType();
}
