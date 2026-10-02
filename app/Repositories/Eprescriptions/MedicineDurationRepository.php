<?php

namespace App\Repositories\Eprescriptions;

use App\Interfaces\Eprescriptions\MedicineDurationRepositoryInterface;
use App\Models\MedicineDuration;
use Illuminate\Support\Facades\Auth;

class MedicineDurationRepository implements MedicineDurationRepositoryInterface
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

    public function all()
    {
        return MedicineDuration::latest()->get();
    }

    public function paginate($search, int $perPage = 15)
    {
        $medicineDurations = MedicineDuration::query();

        if ($search) {
            $medicineDurations->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('days', 'like', "%{$search}%")
            );
        }

        $medicineDurations = $medicineDurations->where('doctor_id', $this->doctor_id);

        $medicineDurations = $medicineDurations->orderBy('days')->latest()->paginate($perPage)->withQueryString();

        return $medicineDurations;
    }

    public function find(int $id): ?MedicineDuration
    {
        return MedicineDuration::find($id);
    }

    public function create(array $data): MedicineDuration
    {
        $data += ['doctor_id' => $this->doctor_id];

        return MedicineDuration::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $duration = $this->find($id);

        return $duration ? $duration->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $duration = $this->find($id);

        return $duration ? $duration->delete() : false;
    }
}
