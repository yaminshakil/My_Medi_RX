<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Division;

class LocationController extends Controller
{
    public function getDivisions()
    {
        return Division::select('id', 'name', 'latitude', 'longitude')->get();
    }

    public function getDistricts(Division $division)
    {
        return $division->districts()->select('id', 'name', 'latitude', 'longitude')->get();
    }

    public function getThanas(District $district)
    {
        return $district->thanas()->select('id', 'name')->get();
    }
}
