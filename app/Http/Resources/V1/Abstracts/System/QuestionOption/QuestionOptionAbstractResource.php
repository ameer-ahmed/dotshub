<?php

namespace App\Http\Resources\V1\Abstracts\System\QuestionOption;

use App\Http\Resources\PlatformResource;

abstract class QuestionOptionAbstractResource extends PlatformResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // TODO: add more fields
        ];
    }
}
