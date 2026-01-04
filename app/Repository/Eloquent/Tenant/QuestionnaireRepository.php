<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\Questionnaire;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\Contracts\Tenant\QuestionnaireRepositoryInterface;

class QuestionnaireRepository extends Repository implements QuestionnaireRepositoryInterface
{
    protected Model $model;

    public function __construct(Questionnaire $model)
    {
        parent::__construct($model);
    }
}
