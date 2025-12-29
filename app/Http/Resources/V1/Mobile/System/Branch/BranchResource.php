<?php

namespace App\Http\Resources\V1\Mobile\System\Branch;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\Branch\BranchAbstractResource;

class BranchResource extends BranchAbstractResource
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
