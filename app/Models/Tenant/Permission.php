<?php

namespace App\Models\Tenant;

use Laratrust\Models\Permission as PermissionModel;
use Spatie\Translatable\HasTranslations;

class Permission extends PermissionModel
{
    use HasTranslations;

    public $guarded = [];
    protected $casts = [
        'display_name' => 'json'
    ];
    public array $translatable = ['display_name'];
}
