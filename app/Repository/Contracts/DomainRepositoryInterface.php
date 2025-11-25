<?php

namespace App\Repository\Contracts;

use App\Repository\Contracts;

interface DomainRepositoryInterface extends Contracts\RepositoryInterface
{
    public function isUnique($domain);
}
