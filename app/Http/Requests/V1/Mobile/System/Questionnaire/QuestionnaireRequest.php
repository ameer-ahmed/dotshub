<?php

namespace App\Http\Requests\V1\Mobile\System\Questionnaire;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractRequest;

class QuestionnaireRequest extends QuestionnaireAbstractRequest
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
