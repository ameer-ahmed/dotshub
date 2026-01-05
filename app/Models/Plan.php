<?php

namespace App\Models;

use App\Traits\HasTranslationWithLanguageToggle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Plan extends Model
{
    use HasFactory, HasTranslationWithLanguageToggle;
    protected $guarded = [];

    public array $translatable = ['name', 'description'];

}
