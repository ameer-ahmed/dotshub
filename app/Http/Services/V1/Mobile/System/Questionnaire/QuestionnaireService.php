<?php

namespace App\Http\Services\V1\Mobile\System\Questionnaire;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractService;

class QuestionnaireService extends QuestionnaireAbstractService
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }
}
