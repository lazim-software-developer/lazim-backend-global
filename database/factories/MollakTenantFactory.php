<?php

namespace Database\Factories;

use App\Models\MollakTenant;
use App\Models\Building\Flat;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MollakTenant>
 */
class MollakTenantFactory extends Factory
{
    protected $model = MollakTenant::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'contract_number' => 'CN-' . $this->faker->unique()->numberBetween(1000, 9999),
            'emirates_id' => $this->faker->numerify('784-####-#######-#'),
            'passport' => 'P' . $this->faker->numberBetween(1000000, 9999999),
            'license_number' => 'LIC' . $this->faker->unique()->numberBetween(1000, 9999),
            'mobile' => '9715' . $this->faker->numberBetween(10000000, 99999999),
            'email' => $this->faker->unique()->safeEmail(),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'contract_status' => $this->faker->randomElement(['active', 'expired']),
            'building_id' => Building::inRandomOrder()->first()->id ?? Building::factory(),
            'flat_id' => Flat::inRandomOrder()->first()->id ?? Flat::factory(),
            'owner_association_id' => OwnerAssociation::inRandomOrder()->first()->id ?? OwnerAssociation::factory(),
            'resource' => 'system',
        ];
    }
}
