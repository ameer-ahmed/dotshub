<?php

namespace App\Http\Services\V1\Mobile\System\QuestionnaireQuestion;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\System\QuestionnaireQuestion\QuestionnaireQuestionAbstractService;

class QuestionnaireQuestionService extends QuestionnaireQuestionAbstractService
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }
}
