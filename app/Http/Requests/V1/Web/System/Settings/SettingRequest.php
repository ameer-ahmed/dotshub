<?php

namespace App\Http\Requests\V1\Web\System\Settings;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\System\Settings\SettingAbstractRequest;

class SettingRequest extends SettingAbstractRequest
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
