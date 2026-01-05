<?php

namespace App\Http\Requests\V1\Abstracts\System\Question;

use App\Enums\QuestionType;
use App\Http\Requests\PlatformRequest;
use Illuminate\Validation\Rule;

abstract class QuestionAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title.ar' => [Rule::requiredIf(!$this->input('title.en')), 'string', 'max:255'],
            'title.en' => [Rule::requiredIf(!$this->input('title.ar')), 'string', 'max:255'],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'icon' => ['nullable', 'string'],
            'options' => [
                Rule::requiredIf(in_array($this->input('type'), QuestionType::hasRequiredOptions())),
                Rule::excludeIf(!in_array($this->input('type'), QuestionType::hasRequiredOptions())),
                'array'
            ],
            'options.*.sort' => ['nullable', 'integer', 'gte:1']
        ];

        if ($this->isMethod('PUT')) {
            $rules['options'][0] = empty($this->input('existing_options'))
                ? Rule::requiredIf(in_array($this->input('type'), QuestionType::hasRequiredOptions()))
                : 'nullable';
            $rules['existing_options'] = [
                empty($this->input('options')) ? Rule::requiredIf(in_array($this->input('type'), QuestionType::hasRequiredOptions())) : 'nullable',
                Rule::excludeIf(!in_array($this->input('type'), QuestionType::hasRequiredOptions())),
                'array'
            ];
            $rules['existing_options.*.id'] = ['required', Rule::exists('question_options', 'id')->where('question_id', $this->id)];
            $rules['existing_options.*.sort'] = ['nullable', 'integer', 'gte:1'];
            foreach ($this->input('existing_options') ?? [] as $i => $existingOption) {
                $rules['existing_options.'.$i.'.option.ar'] = [Rule::requiredIf(!$this->input('existing_options.'.$i.'.option.en')), 'string', 'max:255'];
                $rules['existing_options.'.$i.'.option.en'] = [Rule::requiredIf(!$this->input('existing_options.'.$i.'.option.ar')), 'string', 'max:255'];
            }
        }

        foreach ($this->input('options') ?? [] as $i => $option) {
            $rules['options.'.$i.'.option.ar'] = [Rule::requiredIf(!$this->input('options.'.$i.'.option.en')), 'string', 'max:255'];
            $rules['options.'.$i.'.option.en'] = [Rule::requiredIf(!$this->input('options.'.$i.'.option.ar')), 'string', 'max:255'];
        }

        return $rules;
    }
}
