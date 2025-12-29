<?php

namespace App\Http\Services\V1\Mobile\System\Settings;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\System\Settings\SettingAbstractService;

class SettingService extends SettingAbstractService
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }
}
