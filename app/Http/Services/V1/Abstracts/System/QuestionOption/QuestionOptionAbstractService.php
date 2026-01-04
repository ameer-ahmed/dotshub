<?php

namespace App\Http\Services\V1\Abstracts\System\QuestionOption;

use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\QuestionOptionRepositoryInterface;

abstract class QuestionOptionAbstractService extends PlatformService
{
    public function __construct(
        private readonly QuestionOptionRepositoryInterface $questionOptionRepository
    )
    {
    }

    public function store($options)
    {

    }
}
