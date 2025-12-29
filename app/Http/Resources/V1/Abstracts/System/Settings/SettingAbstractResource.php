<?php

namespace App\Http\Resources\V1\Abstracts\System\Settings;

use App\Enums\Platform;
use App\Http\Resources\PlatformResource;

abstract class SettingAbstractResource extends PlatformResource
{
    // Concrete implementations (Web/Mobile) will implement platform() method
    // and define their own toArray() method
}
