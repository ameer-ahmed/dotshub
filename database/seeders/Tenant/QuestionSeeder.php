<?php

namespace Database\Seeders\Tenant;

use App\Enums\QuestionType;
use App\Models\Tenant\Question;
use App\Models\Tenant\Questionnaire;
use App\Models\Tenant\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                'title' => [
                    'ar' => 'الاسم',
                    'en' => 'Name'
                ],
                'created_by' => User::first()->id,
                'type' => QuestionType::TEXT->value,
            ],
            [
                'title' => [
                    'ar' => 'البريد الالكتروني',
                    'en' => 'Email'
                ],
                'created_by' => User::first()->id,
                'type' => QuestionType::EMAIL->value,
            ],
            [
                'title' => [
                    'ar' => 'رقم الهاتف',
                    'en' => 'Phone'
                ],
                'created_by' => User::first()->id,
                'type' => QuestionType::MOBILE->value,
            ],
            [
                'title' => [
                    'ar' => 'ملحوظات',
                    'en' => 'Note'
                ],
                'created_by' => User::first()->id,
                'type' => QuestionType::TEXT->value,
            ],
        ];

        $models = collect();

        foreach ($questions as $question) {
            $models->add(Question::query()->create($question));
        }

        $questionnaire = Questionnaire::query()->create([
            'title' => [
                'ar' => 'إنشاء عميل محتمل',
                'en' => 'Add new lead'
            ],
            'created_by' => User::first()->id,
        ]);

        $attributes = [
            ['created_by' => User::first()->id, 'order' => 1, 'is_required' => true, 'is_unique' => false, 'is_main' => true, 'is_filterable' => false, 'is_filterable_by_range' => false, 'is_shown' => true, 'is_hidable' => false],
            ['created_by' => User::first()->id, 'order' => 2, 'is_required' => false, 'is_unique' => false, 'is_main' => false, 'is_filterable' => false, 'is_filterable_by_range' => false, 'is_shown' => false, 'is_hidable' => false],
            ['created_by' => User::first()->id, 'order' => 3, 'is_required' => true, 'is_unique' => true, 'is_main' => false, 'is_filterable' => false, 'is_filterable_by_range' => false, 'is_shown' => true, 'is_hidable' => false],
            ['created_by' => User::first()->id, 'order' => 4, 'is_required' => false, 'is_unique' => false, 'is_main' => false, 'is_filterable' => false, 'is_filterable_by_range' => false, 'is_shown' => true, 'is_hidable' => false],
        ];

        foreach ($models as $i => $model) {
            $questionnaire->questions()->attach($model->id, $attributes[$i]);
        }
    }
}
