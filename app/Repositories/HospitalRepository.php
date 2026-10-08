<?php

namespace App\Repositories;

use App\Interfaces\HospitalRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\HospitalResource;
use App\Models\Hospital;
use App\Models\HospitalType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HospitalRepository implements HospitalRepositoryInterface
{
    public function getAllHospital($perPage = 10, $search = null, $verification_status = null)
    {
        // $hospitals = Hospital::where('status', 1);

        // if ($search) {
        //     $hospitals->where('hospital_name', 'like', "%{$search}%");
        // }

        // $hospitals = $hospitals->orderBy('sort_order', 'ASC')
        //     ->paginate($perPage)
        //     ->withQueryString();

        $hospitals = Hospital::query()
            ->with('hospitalType')
            ->when(
                $search,
                fn ($query, $search) =>
                    $query->where(function ($q) use ($search) {
                        $q->where('hospital_name', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
            )
            ->when(
                $verification_status,
                fn ($query, $status) =>
                    $query->where('verification_status', $status)
            )
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return $hospitals;
    }

    public function getHospital()
    {
        return HospitalResource::collection(Hospital::orderBy('sort_order', 'ASC')->get());
    }

    public function getHospitalById($uuid)
    {
        $hospital = Hospital::where('id', $uuid)->get();
        $hospital->load([
            'doctors' => fn ($q) => $q->with(['profile', 'specialties', 'specialties.parent', 'chambers']),
        ]);

        return HospitalResource::collection($hospital);
    }

    public function createHospital($data)
    {
        $hospital = null;
        DB::transaction(function () use ($data, &$hospital) {
            $hospital = new Hospital();
            $hospital->hospital_name = $data['hospital_name'];
            $hospital->hospital_type_id = $data['hospital_type_id'];
            $hospital->address = $data['address'];
            $hospital->hospital_description = $data['hospital_description'] ?? null;
            $hospital->email = $data['email'] ?? null;
            $hospital->mobile_number = $data['mobile_number'] ?? null;
            $hospital->emergency_contact = $data['emergency_contact'] ?? null;
            $hospital->phone_number = $data['phone_number'] ?? null;
            if (!empty($data['hospital_logo']) && $data['hospital_logo'] instanceof \Illuminate\Http\UploadedFile) {
                $hospital->hospital_logo = $data['hospital_logo']->store('hospitals', 'public');
            }
            if (!empty($data['banner_url']) && $data['banner_url'] instanceof \Illuminate\Http\UploadedFile) {
                $hospital->banner_url = $data['banner_url']->store('hospitals', 'public');
            }
            $hospital->sort_order = 0;
            $hospital->status = 0; // Set default status to inactive
            $hospital->verification_status = 'pending'; // Set default verification status to pending
            $hospital->created_by = Auth::user()->id;
            $hospital->save();
        });
        // $hospital_logo = null;
        // if (!empty($data['hospital_logo']) && $data['hospital_logo'] instanceof \Illuminate\Http\UploadedFile) {
        //     $data['hospital_logo'] = $data['hospital_logo']->store('hospitals', 'public');
        // }
        // if (!empty($data['banner_url']) && $data['banner_url'] instanceof \Illuminate\Http\UploadedFile) {
        //     $data['banner_url'] = $data['banner_url']->store('hospitals', 'public');
        // }

        return $hospital;
    }

    public function updateHospital($data, $id)
    {
        $hospital_logo = null;
        if (!empty($data['hospital_logo']) && $data['hospital_logo'] instanceof \Illuminate\Http\UploadedFile) {
            if ($data['prev_hospital_logo'] != null) {
                Storage::disk('public')->delete($data['prev_hospital_logo']);
            }
            $data['hospital_logo'] = $data['hospital_logo']->store('hospitals', 'public');
        } else {
            $data['hospital_logo'] = $data['prev_hospital_logo'];
        }

        if (($data['banner_url'] != null) && $data['banner_url'] instanceof \Illuminate\Http\UploadedFile) {
            if ($data['prev_banner_url'] != null) {
                Storage::disk('public')->delete($data['prev_banner_url']);
            }
            $banner_url = $data['banner_url'];
            $data['banner_url'] = $banner_url->store('hospitals', 'public');
        } else {
            $data['banner_url'] = $data['prev_banner_url'] ?? null;
        }

        unset($data['prev_hospital_logo']);
        unset($data['prev_banner_url']);
        $data += ['updated_by' => Auth::user()->id];
        return Hospital::where('id', $id)->update($data);
    }

    public function deleteHospital($id)
    {
        return Hospital::destroy($id);
    }
}
