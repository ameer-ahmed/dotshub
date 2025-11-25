<?php

namespace App\Http\Resources\V1\Web\Merchant\Role;

use App\Http\Resources\V1\Web\Merchant\Permission\PermissionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->display_name,
            'permissions' => PermissionResource::collection($this->permissions),
        ];
    }
}
