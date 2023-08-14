<?php

namespace Database\Factories\Building;

use App\Models\Building\Building;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Building::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'unit_number' => $this->faker->numerify('##########'),
            'address_line1' => $this->faker->address(),
            'address_line2' => $this->faker->address(),
            'area' => $this->faker->city(),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
            'description' => $this->faker->text(),
            'floors' => $this->faker->numberBetween(1, 10),
            'city_id' => \App\Models\Master\City::factory(),
        ];
    }
}
