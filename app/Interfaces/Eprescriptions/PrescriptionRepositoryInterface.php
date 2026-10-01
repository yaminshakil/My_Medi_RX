<?php

namespace App\Interfaces\Eprescriptions;

interface PrescriptionRepositoryInterface
{
    public function all($filters, $parPage);

    public function find($id);

    public function store(array $data);

    public function delete($id);

    public function getAllMedicine();

    public function getAllPatient();

    public function saveAndNewPrescription();
}
