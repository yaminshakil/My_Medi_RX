<?php

namespace App\Http\Resources\Doctors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalSpecialtyGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => $this['label'],

            'options' => collect($this['options'])->map(fn ($option) => [
                'value' => $option['value'],
                'label' => $option['label'],
            ])->values(),
        ];
    }
}
