<?php

namespace App\Repository\Contracts\Tenant;

use App\Repository\Contracts;

interface RoleRepositoryInterface extends Contracts\RepositoryInterface
{
    public function getMerchantAvailableRoles();
}
