<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\QuestionnaireQuestion;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\Contracts\Tenant\QuestionnaireQuestionRepositoryInterface;

class QuestionnaireQuestionRepository extends Repository implements QuestionnaireQuestionRepositoryInterface
{
    protected Model $model;

    public function __construct(QuestionnaireQuestion $model)
    {
        parent::__construct($model);
    }
}
