<?php

namespace App\DTOs\V1\System\QuestionOption;

readonly class QuestionOptionDto
{
    public function __construct(
        public array $option,
        public int   $question_id,
        public ?int  $id = null,
        public ?int  $sort = 1,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'option' => $this->option,
            'question_id' => $this->question_id,
            'sort' => $this->sort
        ], function ($value) {
            return $value !== null;
        });
    }
}
