<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => [
                'ar' => 'خطة #' . $this->faker->numberBetween(10, 99),
                'en' => 'Plan #' . $this->faker->numberBetween(10, 99),
            ],
            'description' => [
                'ar' => $this->faker->realText(),
                'en' => $this->faker->realText(),
            ],
            'price' => $this->faker->numberBetween(100, 999),
            'is_trial' => false,
            'is_active' => true,
        ];
    }
}
