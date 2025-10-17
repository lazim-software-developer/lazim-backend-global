<?php

namespace Database\Seeders;

use App\Models\User\User;
use App\Models\Building\Flat;
use App\Models\Forms\MoveInOut;
use Illuminate\Database\Seeder;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MoveInOutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

                $ownerAssociation = OwnerAssociation::inRandomOrder()->first() ?? OwnerAssociation::factory()->create();
        $buildings = Building::all()->count() ? Building::all() : Building::factory(3)->create();
        $flats = Flat::all()->count() ? Flat::all() : Flat::factory(10)->create();
        $users = User::all()->count() ? User::all() : User::factory(5)->create();
        
        MoveInOut::factory()
            ->count(10)
            ->moveIn()
            ->create([
                'owner_association_id' => $ownerAssociation->random()->id,
                'building_id' => $buildings->random()->id,
                'flat_id' => $flats->random()->id,
                'user_id' => $users->random()->id,
            ]);

        // Move-Outs
        MoveInOut::factory()
            ->count(10)
            ->moveOut()
            ->create([
                'owner_association_id' => $ownerAssociation->random()->id,
                'building_id' => $buildings->random()->id,
                'flat_id' => $flats->random()->id,
                'user_id' => $users->random()->id,
            ]);

        // Approved and rejected samples
        MoveInOut::factory()
            ->count(5)
            ->approved()
            ->create([
                'owner_association_id' => $ownerAssociation->random()->id,
                'building_id' => $buildings->random()->id,
                'flat_id' => $flats->random()->id,
                'user_id' => $users->random()->id,
            ]);

        MoveInOut::factory()
            ->count(5)
            ->rejected()
            ->create([
                'owner_association_id' => $ownerAssociation->random()->id,
                'building_id' => $buildings->random()->id,
                'flat_id' => $flats->random()->id,
                'user_id' => $users->random()->id,
                'rejected_fields' => json_encode(['contract', 'passport']),
                'remarks' => 'Missing required documents.',
            ]);

        $this->command->info('✅ MoveInOutSeeder: Move-in/out records seeded successfully.');
    }
}
