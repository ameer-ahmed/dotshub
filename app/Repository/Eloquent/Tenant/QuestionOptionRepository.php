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
}
