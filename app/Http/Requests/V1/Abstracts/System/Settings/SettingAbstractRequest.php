<?php

namespace App\Http\Requests\V1\Abstracts\System\Settings;

use App\Http\Requests\PlatformRequest;
use App\Repository\Contracts\Tenant\SettingRepositoryInterface;

abstract class SettingAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $setting = resolve(SettingRepositoryInterface::class)->getById($this->input('id'));

        return [
            'value' => ['required', $setting->type]
        ];
    }
}
