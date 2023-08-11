<?php

namespace Database\Factories;

use App\Models\Master\Facility;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Facility::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'icon' => $this->faker->text(),
            'active' => $this->faker->boolean(),
        ];
    }
}
