<?php

namespace App\Models\Tenant;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = [];

    public function company() {
        return $this->belongsTo(Merchant::class);
    }
}
