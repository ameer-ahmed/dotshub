<?php

namespace App\Http\Resources\V1\Web\System\QuestionOption;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractResource;

class QuestionOptionResource extends QuestionOptionAbstractResource
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
