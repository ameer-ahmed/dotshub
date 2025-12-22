<?php

namespace App\Models\Tenant;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = [];

    public function company() {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
