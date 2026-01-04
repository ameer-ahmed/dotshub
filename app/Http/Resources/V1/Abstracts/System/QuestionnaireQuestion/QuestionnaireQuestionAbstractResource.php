<?php

namespace App\Http\Resources\V1\Abstracts\System\QuestionnaireQuestion;

use App\Http\Resources\PlatformResource;

abstract class QuestionnaireQuestionAbstractResource extends PlatformResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // TODO: add more fields
        ];
    }
}
