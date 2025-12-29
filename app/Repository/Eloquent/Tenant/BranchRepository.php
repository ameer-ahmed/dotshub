<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\Branch;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\Contracts\Tenant\BranchRepositoryInterface;

class BranchRepository extends Repository implements BranchRepositoryInterface
{
    protected Model $model;

    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }
}
