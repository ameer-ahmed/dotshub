<?php

namespace App\Repository\Eloquent;

use App\Models\Merchant;
use App\Repository\Contracts\MerchantRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class MerchantRepository extends Repository implements MerchantRepositoryInterface
{
    protected Model $model;

    public function __construct(Merchant $model)
    {
        parent::__construct($model);
    }
}
