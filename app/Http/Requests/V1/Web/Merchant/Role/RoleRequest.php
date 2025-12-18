<?php

namespace App\Http\Requests\V1\Web\Merchant\Role;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\Merchant\Role\RoleAbstractRequest;

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
