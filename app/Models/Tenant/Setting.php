<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    protected function value(): Attribute
    {
        return Attribute::get(function ($value) {
            return match ($this->type) {
                'integer' => (int) $value,
                'string' => (string) $value,
                'json' => json_decode($value),
                'boolean' => (boolean) $value
            };
        });
    }
}
