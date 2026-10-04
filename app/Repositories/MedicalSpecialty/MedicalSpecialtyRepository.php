<?php

namespace App\Repositories\MedicalSpecialty;

use App\Interfaces\MedicalSpecialty\MedicalSpecialtyRepositoryInterface;
use App\Models\MedicalSpecialty;

class MedicalSpecialtyRepository implements MedicalSpecialtyRepositoryInterface
{
    public function all($search, $perPage)
    {
        $locale = app()->getLocale();

        $medicalSpecialty = MedicalSpecialty::query();

        $medicalSpecialty =  $medicalSpecialty->with([
           'translations' => function ($query) use ($locale) {
               $query->where('field', 'name')
                   ->whereIn('locale', [
                       $locale,
                       config('languages.default', 'en'),
                   ]);
           }
        ])
        ->when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%");
        });

        $medicalSpecialty = $medicalSpecialty->with('parent')->latest()->paginate($perPage)->withQueryString();

        return $medicalSpecialty;
    }

    public function find($id): ?MedicalSpecialty
    {
        return MedicalSpecialty::find($id);
    }

    public function create(array $data): MedicalSpecialty
    {
        $translations = $data['translations'] ?? [];

        unset($data['translations']);

        $specialty = MedicalSpecialty::create($data);

        foreach ($translations as $field => $locales) {
            $specialty->setTranslations($field, $locales);
        }
        return $specialty;
    }

    public function update(MedicalSpecialty $specialty, array $data): MedicalSpecialty
    {
        $translations = $data['translations'] ?? [];

        unset($data['translations']);

        // Update main table fields
        $specialty->update($data);

        // Update translations table
        foreach ($translations as $field => $locales) {
            $specialty->setTranslations($field, $locales);
        }

        return $specialty->refresh();
    }

    public function delete(MedicalSpecialty $specialty): bool
    {
        return $specialty->delete();
    }
}
