<?php

namespace App\Http\Services\V1\Abstracts\System\Question;

use App\Enums\QueryReturnType;
use App\Enums\QuestionType;
use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Abstracts\System\Question\QuestionAbstractRequest;
use App\Http\Resources\V1\Abstracts\System\Question\QuestionAbstractResource;
use App\Http\Resources\V1\Abstracts\System\Question\QuestionTypeAbstractResource;
use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\QuestionRepositoryInterface;

abstract class QuestionAbstractService extends PlatformService
{
    public function __construct(
        private readonly QuestionRepositoryInterface $questionRepository
    )
    {
    }

    public function index()
    {
        $questions = $this->questionRepository->query(
            returnType: QueryReturnType::PAGINATE
        );

        return Responser::success(data: QuestionAbstractResource::collection($questions)->response()->getData());
    }

    public function getAll()
    {
        $questions = $this->questionRepository->query(
            returnType: QueryReturnType::GET
        );

        return Responser::success(data: QuestionAbstractResource::collection($questions));
    }

    public function getTypes()
    {
        return Responser::success(data: QuestionTypeAbstractResource::collection(QuestionType::cases()));
    }

    public function store(QuestionAbstractRequest $request)
    {
        $data = $request->only('title', 'type', 'icon');

        $question = $this->questionRepository->create($data);

        return Responser::success(data: QuestionAbstractResource::make($question));
    }

    public function update(QuestionAbstractRequest $request, $id)
    {
        $data = $request->validated();

        $question = $this->questionRepository->update($id, $data);

        return Responser::success(data: QuestionAbstractResource::make($question));
    }

    public function show($id)
    {
        $question = $this->questionRepository->getById($id);

        return Responser::success(data: QuestionAbstractResource::make($question));
    }

    public function destroy($id)
    {
        $this->questionRepository->delete($id);

        return Responser::success(message: __('Deleted successfully'));
    }
}
