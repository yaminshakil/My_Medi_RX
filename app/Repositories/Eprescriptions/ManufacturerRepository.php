<?php

namespace App\Repositories\Eprescriptions;

use App\Interfaces\Eprescriptions\ManufacturerRepositoryInterface;
use App\Models\Manufacturer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ManufacturerRepository implements ManufacturerRepositoryInterface
{
    protected $user;

    protected $doctor_id;

    public function __construct()
    {
        $this->user = Auth::user();
        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = $this->user->doctor_assistant->doctor_id;
        } else {
            $this->doctor_id = $this->user->id;
        }
    }

    public function all($perPage, $search): LengthAwarePaginator
    {
        $manufacturer = Manufacturer::query();
        if ($search) {
            $manufacturer->where(
                fn ($query) => $query->where('company_name', 'like', "%{$search}%")
            );
        }

        $manufacturer = $manufacturer->where('doctor_id', $this->doctor_id);

        $manufacturer = $manufacturer->latest()->paginate($perPage)->withQueryString();

        return $manufacturer;
    }

    public function find(int $id): ?Manufacturer
    {
        return Manufacturer::find($id);
    }

    public function store(array $data): Manufacturer
    {
        $data += ['doctor_id' => $this->doctor_id];

        return Manufacturer::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $manufacturer = Manufacturer::findOrFail($id);

        return $manufacturer->update($data);
    }

    public function delete(int $id): bool
    {
        $manufacturer = Manufacturer::findOrFail($id);

        return $manufacturer->delete();
    }
}
