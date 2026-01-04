<?php

namespace App\Models\Tenant;

use App\Enums\QuestionType;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Question extends Model
{
    use HasTranslations {
        getTranslation as getTranslationTest;
    }
    use SoftDeletes, HasCreatedBy;

    protected $guarded = [];
    protected $casts = [
        'type' => QuestionType::class,
    ];
    public array $translatable = [
        'title'
    ];

//    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
//    {
//        $translation = $this->getTranslationTest($key, $locale, $useFallbackLocale);
//        if (empty($translation)) {
//            $translation = $this->getTranslationTest($key, $locale == 'en' ? 'ar' : 'en', $useFallbackLocale);
//        }
//        return $translation;
//    }
}
