<?php

namespace App\Http\Resources\V1\Mobile\System\QuestionnaireQuestion;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\QuestionnaireQuestion\QuestionnaireQuestionAbstractResource;

class QuestionnaireQuestionResource extends QuestionnaireQuestionAbstractResource
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
