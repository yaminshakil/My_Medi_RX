<?php

namespace App\Repositories\Doctors;

use App\Interfaces\Doctors\DoctorRepositoryInterface;
use App\Models\Doctor;
use App\Models\MedicalSpecialty;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DoctorRepository implements DoctorRepositoryInterface
{
    public function all(): Collection
    {
        return Doctor::all();
    }

    public function create(array $data): ?Doctor
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $profileImagePath = null;
        if (isset($data['profile_image'])) {
            $profileImagePath = $data['profile_image']->store('profiles', 'public');
            $user->profileImage()->create([
                'path' => $profileImagePath,
            ]);
        }

        $doctor = Doctor::create([
            'user_id'           => $user->id,
            'phone'             => $data['phone'] ?? null,
            'gender'            => $data['gender'] ?? null,
            'dob'               => $data['dob'] ?? null,
            'specialization'    => $data['specialization'] ?? null,
            'working_institute' => $data['working_institute'] ?? null,
            'registration_no'   => $data['registration_no'] ?? null,
            'designation'       => $data['designation'] ?? null,
            'qualification'     => $data['qualification'] ?? null,
            'experience_years'  => $data['experience_years'] ?? null,
            'bio'               => $data['bio'] ?? null,
            'social'            => $data['social'] ?? null,
            'active'            => $data['active'] ?? true,
            'featured'          => $data['featured'] ?? false,
        ]);

        $user->syncRoles('Doctor');
        $user->specialties()->sync($data['specialization_ids']);

        return $doctor;
    }

    public function update(array $data, Doctor $doctor): int
    {
        $user = $doctor->user;

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        $user->specialties()->sync($data['specialization_ids']);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        if (isset($data['profile_image'])) {
            // Delete old image if exists
            if ($user->profileImage) {
                Storage::disk('public')->delete($user->profileImage->path);
                $user->profileImage()->delete();
            }

            // Store new
            $path = $data['profile_image']->store('profiles', 'public');

            $user->profileImage()->create([
                'path' => $path,
            ]);
        }

        $doctor = $doctor->update([
            'phone'             => $data['phone'] ?? null,
            'gender'            => $data['gender'] ?? null,
            'dob'               => $data['dob'] ?? null,
            'specialization'    => $data['specialization'] ?? null,
            'registration_no'   => $data['registration_no'] ?? null,
            'working_institute' => $data['working_institute'] ?? null,
            'designation'       => $data['designation'] ?? null,
            'qualification'     => $data['qualification'] ?? null,
            'experience_years'  => $data['experience_years'] ?? null,
            'bio'               => $data['bio'] ?? null,
            'social'            => $data['social'] ?? null,
            'active'            => $data['active'] ?? true,
            'featured'          => $data['featured'] ?? false,
        ]);

        return $doctor;
    }

    public function delete(Doctor $doctor): bool
    {
        $user = $doctor->user;
        if ($user->profileImage) {
            Storage::disk('public')->delete($user->profileImage->path);
            $user->profileImage()->delete();
        }
        $doctor->user()->delete();

        return $doctor->delete();
    }

    public function find(int $id): ?User
    {
        return User::find($id);
    }

    // Get Medical Specialties for Profile select
    public function getSpecialtiesForSelect()
    {
        $groups = MedicalSpecialty::with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->map(function ($parent) {
                return [
                    'label'   => $parent->name,
                    'options' => $parent->children
                        ->sortBy('name')
                        ->map(fn ($child) => [
                            'value' => $child->id,
                            'label' => $child->name,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()   // ensure sequential keys
            ->all();     // make it a plain array

        return $groups;
    }

    public function getAvailabilityFromTimeSlots($chambers)
    {
        $dayMap = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
        ];

        $availableDayNumbers = [];

        // Collect all available day numbers from all chambers
        foreach ($chambers as $chamber) {
            foreach ($chamber->schedules as $slot) {
                $availableDayNumbers[] = (int) $slot['day'];
            }
        }

        // Remove duplicates
        $availableDayNumbers = array_unique($availableDayNumbers);
        sort($availableDayNumbers);

        // Convert to day names
        $availableDays = array_map(function ($dayNum) use ($dayMap) {
            return $dayMap[$dayNum] ?? 'Unknown';
        }, $availableDayNumbers);

        // Find unavailable days
        $unavailableDays = [];
        foreach ($dayMap as $dayNum => $dayName) {
            if (! in_array($dayNum, $availableDayNumbers)) {
                $unavailableDays[] = $dayName;
            }
        }

        return [
            'availableDays'   => $availableDays,
            'unavailableDays' => $unavailableDays,
        ];
    }

    public function processAvailability($timeSlots)
    {
        $dayMap = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        $shortDayMap = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
        ];

        $days = [];

        // Initialize all days with empty slots
        foreach ($dayMap as $dayNum => $dayName) {
            $days[$dayNum] = [
                'dayName'   => $dayName,
                'shortName' => $shortDayMap[$dayNum],
                'slots'     => [],
            ];
        }

        // Add available slots
        foreach ($timeSlots as $slot) {
            $dayNum = $slot['day'];
            if (isset($days[$dayNum])) {
                $days[$dayNum]['slots'][] = [
                    'start'    => Carbon::parse($slot['start_time'])->format('g:i A'),
                    'end'      => Carbon::parse($slot['end_time'])->format('g:i A'),
                    'duration' => $slot['slot_duration'].' mins',
                ];
            }
        }

        return $days;
    }
}
