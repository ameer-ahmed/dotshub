<?php

namespace App\Http\Resources\V1\Abstracts\System\Branch;

use App\Enums\Platform;
use App\Http\Resources\PlatformResource;
use Illuminate\Http\Request;

abstract class BranchAbstractResource extends PlatformResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location
        ];
    }
}
