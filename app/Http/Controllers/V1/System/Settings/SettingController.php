<?php

namespace App\Http\Controllers\V1\System\Settings;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\Abstracts\System\Settings\SettingAbstractService;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingAbstractService $settingAbstractService,
    ) {}
}
