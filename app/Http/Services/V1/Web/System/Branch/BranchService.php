<?php

namespace App\Http\Services\V1\Web\System\Branch;

use App\Enums\Platform;
use App\Http\Helpers\Responser;
use App\Http\Resources\V1\Abstracts\System\Branch\BranchAbstractResource;
use App\Http\Services\V1\Abstracts\System\Branch\BranchAbstractService;

class BranchService extends BranchAbstractService
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }
}
