<?php

namespace App\Repositories\Eprescriptions;

use App\Interfaces\Eprescriptions\MedicineRepositoryInterface;
use App\Models\Manufacturer;
use App\Models\Medicine;
use Illuminate\Support\Facades\Auth;

class MedicineRepository implements MedicineRepositoryInterface
{
    protected Medicine $model;

    protected $user;

    protected $doctor_id;

    public function __construct(Medicine $model)
    {
        $this->model = $model;
        $this->user = Auth::user();
        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = $this->user->doctor_assistant->doctor_id;
        }
    }

    public function all($perPage, $search)
    {
        $medicines = $this->model->query();

        if ($search) {
            $medicines->where(
                fn ($query) => $query->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
            );
        }

        $medicines = $medicines->where('doctor_id', $this->doctor_id);

        $medicines = $medicines->latest()->paginate($perPage)->withQueryString();

        return $medicines;
    }

    public function find(int $id): ?Medicine
    {
        return $this->model->find($id);
    }

    public function create(array $data): Medicine
    {
        $data += ['doctor_id' => $this->doctor_id];

        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $medicine = $this->find($id);
        if (! $medicine) {
            return false;
        }

        return $medicine->update($data);
    }

    public function delete(int $id): bool
    {
        $medicine = $this->find($id);
        if (! $medicine) {
            return false;
        }

        return $medicine->delete();
    }

    public function getManufacturer()
    {
        return Manufacturer::get();
    }
}
