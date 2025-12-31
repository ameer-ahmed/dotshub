<?php

namespace App\Http\Resources\V1\Web\System\Settings;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\Settings\SettingAbstractResource;

class SettingResource extends SettingAbstractResource
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
