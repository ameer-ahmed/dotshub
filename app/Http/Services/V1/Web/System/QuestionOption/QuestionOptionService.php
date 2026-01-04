<?php

namespace App\Http\Services\V1\Web\System\QuestionOption;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractService;

class QuestionOptionService extends QuestionOptionAbstractService
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }
}
