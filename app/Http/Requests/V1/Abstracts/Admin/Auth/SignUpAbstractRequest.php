<?php

namespace App\Http\Requests\V1\Abstracts\Admin\Auth;

use App\Http\Requests\PlatformRequest;
use Illuminate\Validation\Rules\Password;

abstract class SignUpAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email:rfc,dns'],
            'password' => [Password::min(8)->letters()->numbers()->symbols()]
        ];
    }
}
