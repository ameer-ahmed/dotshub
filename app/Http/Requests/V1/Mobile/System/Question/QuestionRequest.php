<?php

namespace App\Http\Requests\V1\Mobile\System\Question;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\System\Question\QuestionAbstractRequest;

class QuestionRequest extends QuestionAbstractRequest
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
