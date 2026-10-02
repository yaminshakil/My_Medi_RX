<?php

namespace App\Repositories\Eprescriptions;

use App\Interfaces\Eprescriptions\MedicineDoseRepositoryInterface;
use App\Models\MedicineDose;
use Illuminate\Support\Facades\Auth;

class MedicineDoseRepository implements MedicineDoseRepositoryInterface
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

    public function all($perPage, $search)
    {
        $medicineDose = MedicineDose::query();

        if ($search) {
            $medicineDose->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
            );
        }

        $medicineDose = $medicineDose->where('doctor_id', $this->doctor_id);

        $medicineDose = $medicineDose->latest()->paginate($perPage)->withQueryString();

        return $medicineDose;
    }

    public function find(int $id): ?MedicineDose
    {
        return MedicineDose::find($id);
    }

    public function create(array $data): MedicineDose
    {
        $data += ['doctor_id' => $this->doctor_id];

        return MedicineDose::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $dose = $this->find($id);

        return $dose ? $dose->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $dose = $this->find($id);

        return $dose ? $dose->delete() : false;
    }
}
