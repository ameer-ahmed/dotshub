<?php

namespace App\Enums;

use App\Traits\Enumable;

enum UserStatus: int
{
    use Enumable;

    case INACTIVE = 0;
    case ACTIVE = 1;
    case PENDING = 2;
    case SUSPENDED = 3;
}
