<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchFlatsAndOwnersForBuilding;
use App\Models\Building\Building;

class FetchFlatsAndOwnersForBuildingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:flats-and-owners {buildingId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job to fetch flats and owners for a given building.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $buildingId = $this->argument('buildingId');

        // Retrieve the building by ID
        $building = Building::find($buildingId);

        if ($building) {
            // Dispatch the job to fetch flats and owners for the building
            FetchFlatsAndOwnersForBuilding::dispatch($building);
            $this->info("Flats and owners fetch job dispatched for Building ID: {$buildingId}");
        } else {
            $this->error("Building with ID {$buildingId} not found.");
        }
    }
}
