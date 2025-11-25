<?php

namespace App\Http\Requests\V1\Web\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'phone' => ['required', Rule::unique('users', 'phone')->ignore(auth('admin')->id())],
            'email' => ['required', 'email:rfc,dns', Rule::unique('users', 'email')->ignore(auth('admin')->id())],
        ];
    }
}
