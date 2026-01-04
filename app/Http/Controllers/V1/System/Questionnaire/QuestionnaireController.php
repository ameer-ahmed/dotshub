<?php

namespace App\Http\Controllers\V1\System\Questionnaire;

use App\Http\Controllers\Controller;
use App\Http\Services\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractService;

class QuestionnaireController extends Controller
{
    public function __construct(
        private readonly QuestionnaireAbstractService $questionnaireAbstractService,
    ) {}
}
