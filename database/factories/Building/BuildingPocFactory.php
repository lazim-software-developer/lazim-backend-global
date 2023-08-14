<?php

namespace Database\Factories\Building;

use App\Models\Building\BuildingPoc;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingPocFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BuildingPoc::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_name' => $this->faker->name(),
            'escalation_level' => $this->faker->text(50),
            'active' => $this->faker->boolean(),
            'emergency_contact' => $this->faker->boolean(),
            'building_id' => \App\Models\Building\Building::factory(),
            'user_id' => \App\Models\User\User::factory(),
        ];
    }
}
