<?php

namespace App\Http\Services\V1\Abstracts\System\Settings;

use App\Enums\QueryReturnType;
use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Abstracts\System\Settings\SettingAbstractRequest;
use App\Http\Resources\V1\Abstracts\System\Settings\SettingAbstractResource;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\SettingRepositoryInterface;

abstract class SettingAbstractService extends PlatformService
{
    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository,
    )
    {
    }

    public function index()
    {
        $branches = $this->settingRepository->query(
            returnType: QueryReturnType::GET
        );

        return Responser::success(message: __('Retrieved successfully'), data: SettingAbstractResource::collection($branches));
    }

    public function update(SettingAbstractRequest $request, $id)
    {
        $data = $request->validated();
        $setting = $this->settingRepository->update($id, $data);

        return Responser::success(message: __('Updated successfully'), data: SettingAbstractResource::make($setting));
    }
}
