<?php

namespace App\Http\Requests\V1\Mobile\System\Branch;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\System\Branch\BranchAbstractRequest;

class BranchRequest extends BranchAbstractRequest
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
