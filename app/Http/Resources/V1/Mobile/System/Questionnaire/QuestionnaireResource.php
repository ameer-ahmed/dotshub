<?php

namespace App\Http\Resources\V1\Mobile\System\Questionnaire;

use App\Enums\Platform;
use App\Http\Resources\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractResource;

class QuestionnaireResource extends QuestionnaireAbstractResource
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
