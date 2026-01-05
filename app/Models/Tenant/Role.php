<?php

namespace App\Models\Tenant;

use App\Traits\HasTranslationWithLanguageToggle;
use Laratrust\Models\Role as RoleModel;

class Role extends RoleModel
{
    use HasTranslationWithLanguageToggle;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'merchant_id',
        'created_by',
        'is_private',
        'is_editable'
    ];
    protected $casts = [
        'display_name' => 'json',
        'description' => 'json'
    ];
    public array $translatable = [
        'display_name',
        'description'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
