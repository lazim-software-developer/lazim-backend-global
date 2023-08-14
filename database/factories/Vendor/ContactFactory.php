<?php

namespace Database\Factories\Vendor;

use App\Models\Vendor\Contact;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

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
            'email' => $this->faker->unique->email(),
            'designation' => $this->faker->jobTitle(),
            'contactable_type' => $this->faker->randomElement([
                \App\Models\Vendor\Vendor::class,
            ]),
            'contactable_id' => 1,
            
        ];
    }
}
