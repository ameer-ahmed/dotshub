<?php

namespace App\Http\Resources\V1\Web\System\Question;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\Question\QuestionTypeAbstractResource;

class QuestionTypeResource extends QuestionTypeAbstractResource
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
