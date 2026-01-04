<?php

namespace App\Http\Requests\V1\Web\System\Question;

use App\Enums\Platform;
use App\Http\Requests\V1\Abstracts\System\Question\QuestionAbstractRequest;

class QuestionRequest extends QuestionAbstractRequest
{
    public static function platform(): Platform
    {
        return Platform::WEB;
    }

    public function rules(): array
    {
        return parent::rules();
    }
}
