<?php

namespace App\Http\Resources\V1\Abstracts\System\Question;

use App\Http\Resources\PlatformResource;
use App\Http\Resources\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractResource;

abstract class QuestionAbstractResource extends PlatformResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_translations' => $this->getTranslations('title'),
            'type' => $this->type,
            'icon' => $this->icon,
            'options' => QuestionOptionAbstractResource::collection($this->options)
        ];
    }
}
