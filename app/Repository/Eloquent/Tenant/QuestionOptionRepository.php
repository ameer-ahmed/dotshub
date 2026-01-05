<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\QuestionOption;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\Contracts\Tenant\QuestionOptionRepositoryInterface;

class QuestionOptionRepository extends Repository implements QuestionOptionRepositoryInterface
{
    protected Model $model;

    public function __construct(QuestionOption $model)
    {
        parent::__construct($model);
    }

    public function syncExistingOptions(int $questionId, array $optionsIds = [])
    {
        return $this->model::query()->where('question_id', $questionId)->whereNotIn('id', $optionsIds)->delete();
    }
}
