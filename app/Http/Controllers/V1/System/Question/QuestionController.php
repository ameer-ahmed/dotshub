<?php

namespace App\Http\Controllers\V1\System\Question;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Abstracts\System\Question\QuestionAbstractRequest;
use App\Http\Services\V1\Abstracts\System\Question\QuestionAbstractService;
use App\Http\Services\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractService;

class QuestionController extends Controller
{
    public function __construct(
        private readonly QuestionAbstractService $questionAbstractService,
    ) {}

    public function index()
    {
        return $this->questionAbstractService->index();
    }

    public function getAll()
    {
        return $this->questionAbstractService->getAll();
    }

    public function getTypes()
    {
        return $this->questionAbstractService->getTypes();
    }

    public function store(QuestionAbstractRequest $request)
    {
        return $this->questionAbstractService->store($request);
    }

    public function update(QuestionAbstractRequest $request, $id)
    {
        return $this->questionAbstractService->update($request, $id);
    }

    public function show($id)
    {
        return $this->questionAbstractService->show($id);
    }

    public function destroy($id)
    {
        return $this->questionAbstractService->destroy($id);
    }
}
