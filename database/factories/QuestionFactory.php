<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Form;
use App\Models\Question;

class QuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Question::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'type' => fake()->regexify('[A-Za-z0-9]{50}'),
            'content' => fake()->paragraphs(3, true),
            'is_required' => fake()->boolean(),
            'order' => fake()->numberBetween(-10000, 10000),
        ];
    }
}
