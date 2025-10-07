<?php

namespace Database\Factories\Building;

use App\Models\Building\Flat;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Flat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'floor' => $this->faker->randomNumber(),
            'property_number' => $this->faker->randomNumber(),
            'property_type' => 'UNIT',
            'description' => $this->faker->text(50),
            'building_id' => \App\Models\Building\Building::factory(),
            'owner_association_id' => \App\Models\User\User::factory(),
            'status' => 1,
        ];
    }
}
