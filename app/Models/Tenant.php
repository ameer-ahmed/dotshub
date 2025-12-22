<?php

namespace App\Models;

use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $table = 'tenants';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'description', 'status'];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'description',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}
