<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\Role;
use App\Repository\Contracts\Tenant\RoleRepositoryInterface;
use App\Repository\Eloquent\Repository;
use Illuminate\Database\Eloquent\Model;

class RoleRepository extends Repository implements RoleRepositoryInterface
{
    protected Model $model;

    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function getMerchantAvailableRoles()
    {
        return $this->model::query()
            ->with('permissions')
            ->where('merchant_id', auth('user')->user()->merchant_id)
            ->orWhere(function ($query) {
                $query->whereNull('merchant_id');
                $query->where('is_private', false);
            })
            ->get();
    }
}
