<?php

namespace App\Http\Services\V1\Mobile\System\QuestionOption;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractService;

class QuestionOptionService extends QuestionOptionAbstractService
{
    public static function platform(): Platform
    {
        return Platform::MOBILE;
    }
}
