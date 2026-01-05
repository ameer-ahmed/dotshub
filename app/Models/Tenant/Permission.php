<?php

namespace App\Models\Tenant;

use App\Traits\HasTranslationWithLanguageToggle;
use Laratrust\Models\Permission as PermissionModel;


class Permission extends PermissionModel
{
    use HasTranslationWithLanguageToggle;

    public $guarded = [];
    protected $casts = [
        'display_name' => 'json'
    ];
    public array $translatable = ['display_name'];
}
