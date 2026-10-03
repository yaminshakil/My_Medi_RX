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

    /**
     * Save one translation.
     */
    public function setTranslation(
        string $field,
        string $locale,
        ?string $value
    ): Translation {
        return $this->translations()->updateOrCreate(
            [
                'field' => $field,
                'locale' => $locale,
            ],
            [
                'value' => $value ?? '',
            ]
        );
    }

    /**
     * Save multiple translations.
     *
     * Example:
     *
     * [
     *     'en' => 'Cardiology',
     *     'bn' => 'হৃদরোগ',
     *     'ar' => 'أمراض القلب',
     * ]
     */
    public function setTranslations(
        string $field,
        array $translations
    ): void {
        $supportedLocales = array_keys(
            config('languages.supported', [])
        );

        foreach ($translations as $locale => $value) {
            if (!in_array(
                $locale,
                $supportedLocales,
                true
            )) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $this->setTranslation(
                $field,
                $locale,
                $value
            );
        }
    }

    /**
     * Get translation for current locale.
     */
    public function translated(
        string $field,
        ?string $locale = null
    ): ?string {
        $locale ??= app()->getLocale();

        $value = $this->translations()
            ->where('field', $field)
            ->where('locale', $locale)
            ->value('value');

        if ($value !== null && $value !== '') {
            return $value;
        }

        // Fallback to default language
        $defaultLocale = config(
            'languages.default',
            'en'
        );

        if ($locale !== $defaultLocale) {
            return $this->translations()
                ->where('field', $field)
                ->where('locale', $defaultLocale)
                ->value('value');
        }

        return null;
    }

    /**
     * Get all translations for a field.
     */
    public function getTranslations(
        string $field
    ): array {
        return $this->translations()
            ->where('field', $field)
            ->pluck('value', 'locale')
            ->toArray();
    }
}
