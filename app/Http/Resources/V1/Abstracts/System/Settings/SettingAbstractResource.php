<?php

namespace App\Http\Resources\V1\Abstracts\System\Settings;

use App\Enums\Platform;
use App\Http\Resources\PlatformResource;
use Illuminate\Http\Request;

abstract class SettingAbstractResource extends PlatformResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'value' => $this->value,
            'type' => $this->type,
            'group' => $this->group
        ];
    }
}
