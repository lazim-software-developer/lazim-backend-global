<?php

namespace Database\Factories\Building;

use Illuminate\Support\Str;
use App\Models\Building\FacilityBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityBookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FacilityBooking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'order_id' => $this->faker->text(50),
            'payment_status' => $this->faker->text(50),
            'remarks' => [],
            'reference_number' => $this->faker->text(50),
            'approved' => $this->faker->boolean(),
            'facility_id' => \App\Models\Master\Facility::factory(),
            'user_id' => \App\Models\User\User::factory(),
            'approved_by' => \App\Models\User\User::factory(),
        ];
    }
}
