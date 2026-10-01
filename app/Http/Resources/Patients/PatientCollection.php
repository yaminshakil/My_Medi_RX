<?php

namespace App\Http\Resources\Patients;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PatientCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'patients' => PatientResource::collection($this->collection),
            'filters'  => [
                'search'         => $request->input('search'),
                'per_page'       => $request->input('per_page'),
                'sort_by'        => $request->input('sort_by'),
                'sort_direction' => $request->input('sort_direction'),
            ],
        ];
    }
}
