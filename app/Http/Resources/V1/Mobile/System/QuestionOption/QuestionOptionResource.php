<?php

namespace App\Http\Resources\V1\Mobile\System\QuestionOption;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractResource;

class QuestionOptionResource extends QuestionOptionAbstractResource
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
