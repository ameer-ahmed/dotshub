<?php

namespace App\Models\Tenant;

use App\Enums\QuestionType;
use App\Traits\HasCreatedBy;
use App\Traits\HasTranslationWithLanguageToggle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class Question extends Model
{
    use HasTranslationWithLanguageToggle;
    use SoftDeletes, HasCreatedBy;

    protected $fillable = [
        'title', 'icon', 'created_by', 'type'
    ];
    protected $casts = [
        'type' => QuestionType::class,
    ];
    public array $translatable = [
        'title'
    ];

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }
}
