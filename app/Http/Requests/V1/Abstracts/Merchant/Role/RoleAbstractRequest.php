<?php

namespace App\Http\Requests\V1\Abstracts\Merchant\Role;

use App\Http\Requests\PlatformRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

abstract class RoleAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name.ar' => ['required', 'string', 'max:255'],
            'display_name.en' => ['required', 'string', 'max:255'],
            'description.ar' => ['nullable', 'string'],
            'description.en' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', Rule::exists('permissions', 'name')]
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->isMethod('POST')) {
            $this->merge([
                'name' => Str::snake($this->input('display_name.en') . ' ' . Str::uuid())
            ]);
        }
    }
}
