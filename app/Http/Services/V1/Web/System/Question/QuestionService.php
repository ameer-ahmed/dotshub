<?php

namespace App\Http\Services\V1\Web\System\Question;

use App\Enums\Platform;
use App\Http\Services\V1\Abstracts\System\Question\QuestionAbstractService;

class QuestionService extends QuestionAbstractService
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }
}
