<?php

namespace Database\Factories\Building;

use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\FlatTenant;
use App\Models\User\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Complaint::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //'complaint_type' => $this->faker->text(50),
            'category' => $this->faker->word(),
            'open_time' => $this->faker->dateTime(),
            'close_time' => $this->faker->dateTime(),
            'photo' => [],
            'remarks' => [],
            'status' => $this->faker->word(),
            'user_id' => User::factory(),
            'complaintable_type' => $this->faker->randomElement([
                Building::class,
                FlatTenant::class,
            ]),
            'complaintable_id' =>$this->faker->randomNumber(1, 100),
        ];
    }
}
