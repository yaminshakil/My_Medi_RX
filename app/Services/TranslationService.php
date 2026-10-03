<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    public function get(
        string $group,
        string $key,
        ?string $locale = null
    ): ?string {
        $locale ??= app()->getLocale();

        return Translation::query()
            ->where('group', $group)
            ->where('key', $key)
            ->where('locale', $locale)
            ->value('value');
    }

    public function set(
        string $group,
        string $key,
        string $locale,
        string $value
    ): Translation {
        return Translation::updateOrCreate(
            [
                'group' => $group,
                'key' => $key,
                'locale' => $locale,
            ],
            [
                'value' => $value,
            ]
        );
    }

    public function all(): array
    {
        return Translation::query()
            ->whereNull('translatable_type')
            ->whereNull('translatable_id')
            ->whereNull('field')
            ->get()
            ->groupBy('group')
            ->map(function ($items) {
                return $items
                    ->where('locale', app()->getLocale())
                    ->pluck('value', 'key')
                    ->toArray();
            })
            ->toArray();
    }
}
