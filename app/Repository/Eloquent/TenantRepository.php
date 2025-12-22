<?php

namespace App\Repository\Eloquent;

use App\Models\Tenant;
use App\Repository\Contracts\TenantRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class TenantRepository extends Repository implements TenantRepositoryInterface
{
    protected Model $model;

    public function __construct(Tenant $model)
    {
        parent::__construct($model);
    }
}
