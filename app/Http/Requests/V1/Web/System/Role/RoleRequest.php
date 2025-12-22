<?php

namespace App\Http\Requests\V1\Web\System\Role;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\System\Role\RoleAbstractRequest;

class RoleRequest extends RoleAbstractRequest
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
