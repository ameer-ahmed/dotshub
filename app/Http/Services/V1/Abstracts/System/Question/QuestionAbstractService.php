<?php

namespace App\Http\Services\V1\Abstracts\System\Question;

use App\DTOs\V1\System\QuestionOption\QuestionOptionDto;
use App\Enums\QueryReturnType;
use App\Enums\QuestionType;
use App\Http\Helpers\Responser;
use App\Http\Requests\V1\Abstracts\System\Question\QuestionAbstractRequest;
use App\Http\Resources\V1\Abstracts\System\Question\QuestionAbstractResource;
use App\Http\Resources\V1\Abstracts\System\Question\QuestionTypeAbstractResource;
use App\Http\Services\PlatformService;
use App\Http\Services\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractService;
use App\Repository\Contracts\Tenant\QuestionRepositoryInterface;
use Illuminate\Support\Facades\DB;

abstract class QuestionAbstractService extends PlatformService
{
    public function __construct(
        private readonly QuestionRepositoryInterface   $questionRepository,
        private readonly QuestionOptionAbstractService $questionOptionAbstractService,
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

        return DB::transaction(function () use ($request, $data) {
            $question = $this->questionRepository->create($data);

            collect($request->validated('options'))?->each(function ($option) use ($question) {
                $optionDto = new QuestionOptionDto(option: $option['option'], question_id: $question->id, sort: $option['sort'] ?? null);
                $this->questionOptionAbstractService->store($optionDto);
            });

            return Responser::success(data: QuestionAbstractResource::make($question));
        });
    }

    public function update(QuestionAbstractRequest $request, $id)
    {
        $data = $request->only('title', 'type', 'icon');

        return DB::transaction(function () use ($request, $id, $data) {
            $question = $this->questionRepository->update($id, $data);

            $existingOptions = collect($request->validated('existing_options'));

            $this->questionOptionAbstractService->syncExistingOptions($id, $existingOptions->pluck('id')?->toArray() ?? []);

            $existingOptions->each(function ($option) use ($question) {
                $optionDto = new QuestionOptionDto(option: $option['option'], question_id: $question->id, id: $option['id'], sort: $option['sort'] ?? null);
                $this->questionOptionAbstractService->update($optionDto);
            });

            collect($request->validated('options'))?->each(function ($option) use ($question) {
                $optionDto = new QuestionOptionDto(option: $option['option'], question_id: $question->id, sort: $option['sort'] ?? null);
                $this->questionOptionAbstractService->store($optionDto);
            });

            return Responser::success(data: QuestionAbstractResource::make($question));
        });
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
