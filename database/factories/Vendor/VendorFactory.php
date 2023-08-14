<?php

namespace Database\Factories\Vendor;

use App\Models\Vendor\Vendor;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'tl_number' => $this->faker->numerify('##########'),
            'tl_expiry' => $this->faker->date(),
            'status' => $this->faker->word(),
            'remarks' => [],
            'owner_id' => \App\Models\User\User::factory(),
        ];
    }
}
