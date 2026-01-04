<?php

namespace App\Http\Resources\V1\Abstracts\System\Question;

use App\Http\Resources\PlatformResource;

abstract class QuestionTypeAbstractResource extends PlatformResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this->name,
            'translation' => $this->translated(),
            'full_translations' => $this->fullTranslated(),
        ];
    }
}
