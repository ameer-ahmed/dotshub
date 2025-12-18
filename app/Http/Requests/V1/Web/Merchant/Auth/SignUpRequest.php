<?php

namespace App\Http\Requests\V1\Web\Merchant\Auth;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\Merchant\Auth\SignUpAbstractRequest;

class SignUpRequest extends SignUpAbstractRequest
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
