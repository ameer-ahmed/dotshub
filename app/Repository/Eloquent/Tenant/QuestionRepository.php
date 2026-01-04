<?php

namespace App\Repository\Eloquent\Tenant;

use App\Models\Tenant\Question;
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use App\Repository\Contracts\Tenant\QuestionRepositoryInterface;

class QuestionRepository extends Repository implements QuestionRepositoryInterface
{
    protected Model $model;

    public function __construct(Question $model)
    {
        parent::__construct($model);
    }
}
