<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use Illuminate\Http\Request;

class PatrollingController extends Controller
{
    public function featchAllFloors() {
        // Fetch active buildign id
        $buildingId = BuildingPoc::where([
            'user_id' => auth()->user()->id,
            'role_name' => 'security',
            'active' => 1
        ])->value('building_id');

        // Fetch floors
        return $floors = Building::find($buildingId)->floors()->orderBy('floors')->get();

    }

    // Start patrolling API
    
}
