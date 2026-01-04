<?php

namespace App\Http\Requests\V1\Abstracts\System\Questionnaire;

use App\Http\Requests\PlatformRequest;

abstract class QuestionnaireAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}
