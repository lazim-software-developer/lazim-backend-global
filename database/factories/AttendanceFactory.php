<?php

namespace Database\Factories;

use App\Models\Vendor\Attendance;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'entry_time' => $this->faker->time(),
            'exit_time' => $this->faker->time(),
            'attendance' => $this->faker->boolean(),
            'approved_on' => $this->faker->dateTime(),
            'building_id' => \App\Models\Building\Building::factory(),
            'user_id' => \App\Models\User\User::factory(),
            'approved_by' => \App\Models\User\User::factory(),
        ];
    }
}
