<?php

namespace Database\Factories;

use App\Models\Visitor\FlatVisitor;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlatVisitorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FlatVisitor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('##########'),
            'type' => $this->faker->word(),
            'start_time' => $this->faker->dateTime(),
            'end_time' => $this->faker->dateTime(),
            'verification_code' => $this->faker->randomNumber(),
            'remarks' => [],
            'number_of_visitors' => $this->faker->randomNumber(),
            'flat_id' => \App\Models\Building\Flat::factory(),
            'initiated_by' => \App\Models\User\User::factory(),
            'approved_by' => \App\Models\User\User::factory(),
        ];
    }
}
