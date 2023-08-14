<?php

namespace Database\Factories\Building;

use App\Models\Building\FlatTenant;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlatTenantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FlatTenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'primary' => $this->faker->boolean(),
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
            'active' => $this->faker->boolean(),
            'flat_id' => \App\Models\Building\Flat::factory(),
            'tenant_id' => \App\Models\User\User::factory(),
        ];
    }
}
