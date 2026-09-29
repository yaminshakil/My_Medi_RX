<?php

namespace App\Http\Resources\Doctors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalSpecialtiesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'icon'        => $this->icon,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'parent'      => $this->parent,
        ];
    }
}
