<?php

namespace App\Repositories\Eprescriptions;

use App\Interfaces\Eprescriptions\DoctorChamberRepositoryInterface;
use App\Models\DoctorChamber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DoctorChamberRepository implements DoctorChamberRepositoryInterface
{
    protected $user;
    protected $doctor_id;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->doctor_id = null;

        if ($this->user->hasRole('Doctor')) {
            $this->doctor_id = $this->user->id;
        } elseif ($this->user->hasRole('Assistant')) {
            $this->doctor_id = $this->user->doctor_assistant->doctor_id;
        } else {
            $this->doctor_id = $this->user->id;
        }
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return DoctorChamber::where('doctor_id', $this->doctor_id)->get();
    }

    public function create(array $data): ?DoctorChamber
    {
        $chamber_logo = null;

        if ($data['chamber_logo'] != null) {
            $chamber_logo = $data['chamber_logo'];
            $chamber_logoimageOriginalName = $chamber_logo->getClientOriginalName();
            $chamber_logo = $chamber_logo->store('chambers', 'public');
        }

        $doctorchamber = DoctorChamber::create([
            'name' => $data['name'],
            'city' => $data['city'],
            'address' => $data['address'],
            'appoinment_limit' => $data['appoinment_limit'],
            'header_left' => $data['header_left'],
            'header_right' => $data['header_right'],
            'footer_info' => $data['footer_info'],
            'schedules' => $data['schedules'],
            'fee' => $data['fee'],
            'followup_fee' => $data['followup_fee'],
            'report_fee' => $data['report_fee'],
            'chamber_logo' => $chamber_logo,
            'doctor_id' => $this->doctor_id,
            'is_active' => 0,
        ]);

        return $doctorchamber;
    }

    public function update(array $data, int $id): int
    {
        $doctorChamber = DoctorChamber::findOrFail($id);

        if (! empty($data['chamber_logo']) && $data['chamber_logo'] instanceof \Illuminate\Http\UploadedFile) {
            if ($data['prev_chamber_logo'] != null) {
                Storage::disk('public')->delete($data['prev_chamber_logo']);
            }
            $chamber_logo = $data['chamber_logo'];
            $chamber_logoimageOriginalName = $chamber_logo->getClientOriginalName();
            $chamber_logo = $chamber_logo->store('chambers', 'public');

            $doctorchamber = $doctorChamber->update([
                'name' => $data['name'],
                'city' => $data['city'],
                'address' => $data['address'],
                'appoinment_limit' => $data['appoinment_limit'],
                'header_left' => $data['header_left'],
                'header_right' => $data['header_right'],
                'footer_info' => $data['footer_info'],
                'schedules' => $data['schedules'],
                'fee' => $data['fee'],
                'followup_fee' => $data['followup_fee'],
                'report_fee' => $data['report_fee'],
                'chamber_logo' => $chamber_logo,
                'doctor_id' => $this->doctor_id,
            ]);
        } else {
            $doctorchamber = $doctorChamber->update([
                'name' => $data['name'],
                'city' => $data['city'],
                'address' => $data['address'],
                'appoinment_limit' => $data['appoinment_limit'],
                'header_left' => $data['header_left'],
                'header_right' => $data['header_right'],
                'footer_info' => $data['footer_info'],
                'schedules' => $data['schedules'],
                'fee' => $data['fee'],
                'followup_fee' => $data['followup_fee'],
                'report_fee' => $data['report_fee'],
                'doctor_id' => $this->doctor_id,
            ]);
        }
        $doctorChamber->save();

        return $id;
    }

    public function delete(int $id): bool
    {
        $doctorchamber = DoctorChamber::findOrFail($id);

        // If image field exists and is not null
        if (! empty($doctorchamber->chamber_logo) && Storage::disk('public')->exists($doctorchamber->chamber_logo)) {
            Storage::disk('public')->delete($doctorchamber->chamber_logo);
        }

        return $doctorchamber->delete();
    }

    public function find(int $id): ?DoctorChamber
    {
        return DoctorChamber::find($id);
    }

    public function switchChamber($chamber_id)
    {
        // Set all to inactive
        DoctorChamber::query()->where('doctor_id', $this->doctor_id)->update(['is_active' => 0]);

        // Set selected one to active
        DoctorChamber::where('id', $chamber_id)->update(['is_active' => 1]);

        return DoctorChamber::all();
    }
}
