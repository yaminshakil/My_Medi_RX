<?php

namespace App\Http\Resources\Medical;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class MedicalFacilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hospitalType = $this->hospitalType;

        $rawType = $hospitalType?->getAttribute('name')
            ?? $hospitalType?->getAttribute('hospital_type_name')
            ?? $hospitalType?->getAttribute('type')
            ?? 'hospital';

        $type = Str::slug((string) $rawType);

        $iconMap = [
            'hospital' => 'building2',
            'clinic' => 'home',
            'medical-center' => 'building2',
            'emergency' => 'thermometer',
            'pharmacy' => 'pill',
            'diagnostic-center' => 'activity',
        ];

        $colorMap = [
            'hospital' => '#2196F3',
            'clinic' => '#4CAF50',
            'medical-center' => '#2196F3',
            'emergency' => '#00BCD4',
            'pharmacy' => '#9C27B0',
            'diagnostic-center' => '#FF9800',
        ];

        $serviceTime = trim((string) $this->service_time);

        if ($serviceTime === '') {
            $displayStatus = 'Hours not available';
        } elseif (preg_match('/24\s*\/\s*7|24\s*hours/i', $serviceTime)) {
            $displayStatus = 'Open 24/7';
        } else {
            // Display the configured schedule without claiming live status.
            $displayStatus = $serviceTime;
        }

        $distance = (float) $this->distance_km;

        return [
            'id' => $this->uuid ?: 'fac-' . $this->id,
            'name' => $this->hospital_name,
            'type' => $type,
            'distance' => number_format($distance, 1) . ' km away',
            'distanceKm' => round($distance, 2),
            'status' => $displayStatus,
            'phone' => $this->phone_number,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'icon' => $iconMap[$type] ?? 'map-pin',
            'colorHex' => $colorMap[$type] ?? '#607D8B',
            'hasEmergency' => filled($this->emergency_contact),
        ];
    }
}
