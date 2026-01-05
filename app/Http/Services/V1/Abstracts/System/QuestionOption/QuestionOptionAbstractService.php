<?php

namespace App\Http\Services\V1\Abstracts\System\QuestionOption;

use App\DTOs\V1\System\QuestionOption\QuestionOptionDto;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\QuestionOptionRepositoryInterface;

abstract class QuestionOptionAbstractService extends PlatformService
{
    public function __construct(
        private readonly QuestionOptionRepositoryInterface $questionOptionRepository
    )
    {
    }

    public function store(QuestionOptionDto $option): void
    {
        $this->questionOptionRepository->create($option->toArray());
    }

    public function update(QuestionOptionDto $option): void
    {
        $this->questionOptionRepository->update($option->toArray()['id'], $option->toArray());
    }

    public function syncExistingOptions(int $questionId, array $existingOptionsIds = [])
    {
        return $this->questionOptionRepository->syncExistingOptions($questionId, $existingOptionsIds);
    }
}
