<?php

namespace App\Http\Resources\V1\Mobile\System\Settings;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\Settings\SettingAbstractResource;

class SettingResource extends SettingAbstractResource
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // TODO: map fields
        ];
    }
}
