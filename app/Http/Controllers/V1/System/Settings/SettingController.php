<?php

namespace App\Http\Controllers\V1\System\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Abstracts\System\Settings\SettingAbstractRequest;
use App\Http\Services\V1\Abstracts\System\Settings\SettingAbstractService;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingAbstractService $settingAbstractService,
    ) {}

    public function index()
    {
        return $this->settingAbstractService->index();
    }

    public function update(SettingAbstractRequest $request, $id)
    {
        return $this->settingAbstractService->update($request, $id);
    }
}
