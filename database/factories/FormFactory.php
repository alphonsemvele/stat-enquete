<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Form;
use App\Models\User;

class FormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Form::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->text(),
            'created_by' => User::factory(),
            'status' => fake()->regexify('[A-Za-z0-9]{50}'),
            'is_public' => fake()->boolean(),
            'created_by_id' => User::factory(),
        ];
    }
}
