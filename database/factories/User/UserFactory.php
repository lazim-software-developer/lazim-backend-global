<?php

namespace Database\Factories\User;

use App\Models\User\User;
use App\Models\Master\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

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
            'email' => $this->faker->unique->email(),
            'phone' => $this->faker->numerify('##########'),
            'password' => Hash::make('password'),
            'email_verified' => $this->faker->boolean(),
            'phone_verified' => $this->faker->boolean(),
            'active' => $this->faker->boolean(),
            'lazim_id' => $this->faker->unique->text(50),
            'role_id' => Role::factory(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
