<?php

namespace App\Http\Resources\V1\Web\System\Role;

use App\Http\Resources\V1\Web\System\Permission\PermissionResource;
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
