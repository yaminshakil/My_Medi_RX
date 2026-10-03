<?php

namespace App\Traits;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    public function translations(): MorphMany
    {
        return $this->morphMany(
            Translation::class,
            'translatable'
        );
    }

    public function setTranslation(
        string $field,
        string $locale,
        string $value
    ): Translation {
        return $this->translations()->updateOrCreate(
            [
                'field' => $field,
                'locale' => $locale,
            ],
            [
                'value' => $value,
            ]
        );
    }

    public function translated(
        string $field,
        ?string $locale = null
    ): ?string {
        $locale ??= app()->getLocale();

        $translation = $this->translations()
            ->where('field', $field)
            ->where('locale', $locale)
            ->value('value');

        if ($translation) {
            return $translation;
        }

        return $this->translations()
            ->where('field', $field)
            ->where('locale', config('languages.default', 'en'))
            ->value('value');
    }
}
