<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use Illuminate\Http\Request;
use App\Models\Gatekeeper\Patrolling;
use Carbon\Carbon;
use App\Models\Floor;
use App\Http\Resources\GateKeeper\FloorResource;

class PatrollingController extends Controller
{
    public function featchAllFloors() {
        // Fetch active buildign id
        $buildingId = BuildingPoc::where([
            'user_id' => auth()->user()->id,
            'role_name' => 'security',
            'active' => 1
        ])->value('building_id');

        $today = Carbon::today();

        // Fetch floors
        $floors = Patrolling::where(['building_id' => $buildingId])->whereDate('created_at', $today)->get();
        
        return FloorResource::collection($floors);
    }

    // Start patrolling API
    public function store(Request $request, Building $building) {
        $floor = Floor::where(['floors' => $request->input('floor'), 'building_id' => $building->id])->first();

        $today = Carbon::today();

        if(Patrolling::where(['building_id' => $building->id, 'floor_id' => $floor->id])->whereDate('created_at', $today)->exists()) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'There is an entry for this floor created already!',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }
        
        $request->merge([
            'building_id' => $building->id,
            'patrolled_by' => auth()->user()->id,
            'floor_id' => $floor->id,
            'patrolled_at' => now()
        ]);

        Patrolling::Create($request->all());

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
    }
}
