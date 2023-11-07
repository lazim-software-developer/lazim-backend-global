<?php

namespace App\Observers;

use App\Jobs\FetchFlatsAndOwnersForBuilding;
use App\Models\Building\Building;
use Illuminate\Support\Facades\Log;

class BuildingObserver
{
    /**
     * Handle the Building "created" event.
     */
    public function created(Building $building): void
    {
        Log::info("This executed");
        // FetchFlatsAndOwnersForBuilding::dispatch($building);
    }
}
