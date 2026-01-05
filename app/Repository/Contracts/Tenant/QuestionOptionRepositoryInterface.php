<?php

namespace App\Repository\Contracts\Tenant;

interface QuestionOptionRepositoryInterface extends \App\Repository\Contracts\RepositoryInterface
{
    public function syncExistingOptions(int $questionId, array $optionsIds = []);
}
