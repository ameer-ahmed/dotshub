<?php

namespace App\Http\Requests\V1\Web\System\Branch;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\System\Branch\BranchAbstractRequest;

class BranchRequest extends BranchAbstractRequest
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
