<?php

namespace App\Traits;

use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasCreatedBy
{
    protected static function bootHasCreatedBy() {
        static::creating(function ($model) {
            if (!$model->created_by) {
                $model->created_by = auth('user')->id();
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
