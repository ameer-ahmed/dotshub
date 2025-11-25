<?php

namespace App\Http\Services\V1\Web\Admin\Auth;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService;

class AuthService extends AuthAbstractService
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }

    public function whatIsMyPlatform() : string // will be invoked if the request came from website endpoints
    {
        return 'platform: website!';
    }
}
