<?php

namespace App\Traits;

use Spatie\Translatable\HasTranslations;

trait HasTranslationWithLanguageToggle
{
    use HasTranslations {
        getTranslation as getTranslationSpatie;
    }

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
    {
        $translation = $this->getTranslationSpatie($key, $locale, $useFallbackLocale);
        if (empty($translation)) {
            $translation = array_values($this->getTranslations($key))[0];
        }
        return $translation;
    }
}
