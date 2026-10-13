<?php

namespace App\Helpers;

class ShortcodeHelper
{
    public static function parse(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }

        return $template;
    }
}
