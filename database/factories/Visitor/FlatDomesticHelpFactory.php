<?php

namespace Database\Factories\Visitor;

use Illuminate\Support\Str;
use App\Models\Visitor\FlatDomesticHelp;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlatDomesticHelpFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FlatDomesticHelp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->numerify('##########'),
            'profile_photo' => [],
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
            'role_name' => $this->faker->text(50),
            'active' => $this->faker->boolean(),
            'flat_id' => \App\Models\Building\Flat::factory(),
        ];
    }
}
