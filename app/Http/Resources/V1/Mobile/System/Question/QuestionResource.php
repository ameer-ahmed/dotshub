<?php

namespace App\Http\Resources\V1\Mobile\System\Question;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\Question\QuestionAbstractResource;

class QuestionResource extends QuestionAbstractResource
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
