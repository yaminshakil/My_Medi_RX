<?php

namespace App\Repositories;

use App\Interfaces\HospitalRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\HospitalResource;
use App\Models\Hospital;
use Illuminate\Support\Facades\Storage;

class HospitalRepository implements HospitalRepositoryInterface
{
    public function getAllHospital($perPage = 10, $search = null)
    {
        $hospitals = Hospital::where('status', 1);

        if ($search) {
            $hospitals->where('hospital_name', 'like', "%{$search}%");
        }

        $hospitals = $hospitals->orderBy('sort_order', 'ASC')
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
        $hospital_logo = null;
        if (!empty($data['hospital_logo']) && $data['hospital_logo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['hospital_logo'] = $data['hospital_logo']->store('hospitals', 'public');
        }
        if (!empty($data['banner_url']) && $data['banner_url'] instanceof \Illuminate\Http\UploadedFile) {
            $data['banner_url'] = $data['banner_url']->store('hospitals', 'public');
        }
        $data += ['created_by' => Auth::user()->id];
        return Hospital::create($data);
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
