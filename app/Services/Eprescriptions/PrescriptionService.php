<?php

namespace App\Services\Eprescriptions;

use App\Interfaces\Eprescriptions\PrescriptionRepositoryInterface;

class PrescriptionService
{
    protected $prescriptionRepo;

    /**
     * Create a new class instance.
     */
    public function __construct(PrescriptionRepositoryInterface $prescriptionRepo)
    {
        $this->prescriptionRepo = $prescriptionRepo;
    }

    public function all($filters, $parPage)
    {
        return $this->prescriptionRepo->all($filters, $parPage);
    }

    public function find($id)
    {
        return $this->prescriptionRepo->find($id);
    }

    public function store(array $data)
    {
        return $this->prescriptionRepo->store($data);
    }

    public function update($id, array $data)
    {
        return $this->prescriptionRepo->update($id, $data);
    }

    public function delete($id)
    {
        return $this->prescriptionRepo->delete($id);
    }

    public function createPrescription($patient_id, $appointment_id)
    {
        return $this->prescriptionRepo->createPrescription($patient_id, $appointment_id);
    }

    public function getAllMedicine()
    {
        return $this->prescriptionRepo->getAllMedicine();
    }

    public function getAllPatient()
    {
        return $this->prescriptionRepo->getAllPatient();
    }

    public function saveAndNewPrescription()
    {
        return $this->prescriptionRepo->saveAndNewPrescription();
    }
}
