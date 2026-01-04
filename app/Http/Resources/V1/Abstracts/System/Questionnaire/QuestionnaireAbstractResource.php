<?php

namespace App\Http\Resources\V1\Abstracts\System\Questionnaire;

use App\Http\Resources\PlatformResource;

abstract class QuestionnaireAbstractResource extends PlatformResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // TODO: add more fields
        ];
    }
}
