<?php

namespace App\Http\Resources\V1\Web\System\QuestionnaireQuestion;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\QuestionnaireQuestion\QuestionnaireQuestionAbstractResource;

class QuestionnaireQuestionResource extends QuestionnaireQuestionAbstractResource
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
