<?php

namespace App\Repository\Contracts\Tenant;

use App\Repository\Contracts\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function getActiveUsers();
}
