<?php

namespace App\Http\Requests\V1\Abstracts\System\Auth;

use App\Http\Requests\PlatformRequest;

abstract class SignInAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
