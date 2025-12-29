<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\Setting;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\Contracts\Tenant\SettingRepositoryInterface;

class SettingRepository extends Repository implements SettingRepositoryInterface
{
    protected Model $model;

    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }
}
