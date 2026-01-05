<?php

namespace App\Models\Tenant;

use App\Traits\HasCreatedBy;
use App\Traits\HasTranslationWithLanguageToggle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use SoftDeletes, HasTranslationWithLanguageToggle, HasCreatedBy;
    protected $guarded = [];
    public array $translatable = ['title'];
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'questionnaire_question');
    }
}
