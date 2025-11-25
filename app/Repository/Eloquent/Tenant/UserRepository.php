<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\User;
use App\Repository\Contracts\Tenant\UserRepositoryInterface;
use App\Repository\Eloquent\Repository;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends Repository implements UserRepositoryInterface
{
    protected Model $model;

    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getActiveUsers()
    {
        return $this->model::query()->where('is_active', true);
    }
}
