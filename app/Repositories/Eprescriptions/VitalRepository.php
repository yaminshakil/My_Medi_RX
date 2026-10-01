<?php

namespace App\Repositories\Eprescriptions;

use App\Interfaces\Eprescriptions\VitalRepositoryInterface;
use App\Models\Vital;

class VitalRepository implements VitalRepositoryInterface
{
    public function all()
    {
        return Vital::with('patient')->latest()->get();
    }

    public function find($id)
    {
        return Vital::with('patient')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Vital::create($data);
    }

    public function update($id, array $data)
    {
        $vital = Vital::findOrFail($id);
        $vital->update($data);

        return $vital;
    }

    public function delete($id)
    {
        $vital = Vital::findOrFail($id);

        return $vital->delete();
    }
}
