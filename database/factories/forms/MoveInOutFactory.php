<?php

namespace Database\Factories\Forms;

use App\Models\User\User;
use Illuminate\Support\Str;
use App\Models\Building\Flat;
use App\Models\Forms\MoveInOut;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class MoveInOutFactory extends Factory
{
    protected $model = MoveInOut::class;

    public function definition(): array
    {
        // Randomly decide if it's move-in or move-out
        $type = $this->faker->randomElement(['move-in', 'move-out']);

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('+9715########'),

            'approved' => $this->faker->boolean(80),
            'approved_id' => null,

            'building_id' => Building::factory(),
            'flat_id' => Flat::factory(),
            'user_id' => User::factory(),
            'owner_association_id' => OwnerAssociation::factory(),

            'type' => $type,
            'preference' => $this->faker->randomElement(['morning', 'afternoon', 'evening']),
            'time_preference' => $this->faker->time('H:i'),

            'moving_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'moving_time' => $this->faker->time('H:i'),

            'handover_acceptance' => $this->faker->boolean(),
            'receipt_charges' => $this->faker->numberBetween(100, 500),

            // Document flags
            'contract' => $this->faker->boolean(),
            'title_deed' => $this->faker->boolean(),
            'passport' => $this->faker->boolean(),
            'dewa' => $this->faker->boolean(),
            'cooling_registration' => $this->faker->boolean(),
            'gas_registration' => $this->faker->boolean(),
            'vehicle_registration' => $this->faker->boolean(),
            'movers_license' => $this->faker->boolean(),
            'movers_liability' => $this->faker->boolean(),

            // Clearance fields
            'cooling_clearance' => $this->faker->boolean(),
            'gas_clearance' => $this->faker->boolean(),
            'noc_landlord' => $this->faker->boolean(),
            'cooling_final' => $this->faker->boolean(),
            'gas_final' => $this->faker->boolean(),
            'dewa_final' => $this->faker->boolean(),
            'etisalat_final' => $this->faker->boolean(),

            'status' => $this->faker->randomElement(['approved', 'rejected', 'pending']),
            'remarks' => $this->faker->optional()->sentence(),
            'rejected_fields' => null,

            'ticket_number' => strtoupper(Str::random(8)),
        ];
    }

    /**
     * State: Move-In
     */
    public function moveIn(): self
    {
        return $this->state(fn() => ['type' => 'move-in']);
    }

    /**
     * State: Move-Out
     */
    public function moveOut(): self
    {
        return $this->state(fn() => ['type' => 'move-out']);
    }

    /**
     * State: Approved
     */
    public function approved(): self
    {
        return $this->state(fn() => ['status' => 'approved']);
    }

    /**
     * State: Rejected
     */
    public function rejected(): self
    {
        return $this->state(fn() => ['status' => 'rejected']);
    }
}
