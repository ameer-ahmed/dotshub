<?php

namespace App\Http\Services\V1\Mobile\System\Branch;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\System\Branch\BranchAbstractService;

class BranchService extends BranchAbstractService
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }
}
