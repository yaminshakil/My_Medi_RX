<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\District;

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
