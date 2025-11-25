<?php

namespace App\Repository\Eloquent;

use App\Repository\Contracts\DomainRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Domain;

class DomainRepository extends Repository implements DomainRepositoryInterface
{
    protected Model $model;

    public function __construct(Domain $model)
    {
        parent::__construct($model);
    }

    public function isUnique($domain)
    {
        return $this->model::query()->where('domain', $domain)->doesntExist();
    }
}
