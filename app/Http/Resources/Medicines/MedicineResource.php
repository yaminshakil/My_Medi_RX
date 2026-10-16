<?php

namespace App\Http\Resources\Medicines;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'medicine_name' => $this->brand_name,
            'generic_name'  => $this->generic_name,
            'strength'      => $this->strength,
            'medicine_type' => $this->type,
            'manufacturer'  => [
                'name'   => $this->manufacturer?->company_name,
                'status' => $this->manufacturer?->is_active,
            ],
        ];
    }
}
