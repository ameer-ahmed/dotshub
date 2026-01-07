<?php

namespace App\Http\Services\V1\Abstracts\System\Questionnaire;

use App\Http\Services\PlatformService;
use App\Repository\Contracts\Tenant\QuestionnaireRepositoryInterface;
use Illuminate\Support\Str;

abstract class QuestionnaireAbstractService extends PlatformService
{
    public function __construct(
        private readonly QuestionnaireRepositoryInterface $questionnaireRepository,
    )
    {
    }

    public function create($data)
    {
        $data['title']['ar'] = __(class_basename($data['relatable_type']), locale: 'ar') . '_' . Str::snake($data['title']['ar']);
        $data['title']['en'] = __(class_basename($data['relatable_type']), locale: 'en') . '_' . Str::snake($data['title']['en']);

        $this->questionnaireRepository->create($data);

        // TODO: notification event
        // TODO: System log
    }

    public function destroy($id)
    {
        $this->questionnaireRepository->delete($id);
    }
}
