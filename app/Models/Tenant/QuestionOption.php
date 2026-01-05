<?php

namespace App\Models\Tenant;

use App\Traits\HasCreatedBy;
use App\Traits\HasTranslationWithLanguageToggle;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends Model
{
    use HasTranslationWithLanguageToggle, HasCreatedBy, SoftDeletes;

    protected $fillable = [
        'option',
        'question_id',
        'sort',
        'created_by',
    ];
    public array $translatable = ['option'];
}
