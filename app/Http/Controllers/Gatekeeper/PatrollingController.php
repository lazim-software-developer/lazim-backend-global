<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\BuildingPoc;
use Illuminate\Http\Request;

class PatrollingController extends Controller
{
    public function featchAllFloors() {
        // Check if the gatekeeper is having active account inuildingPOC table
        $building = BuildingPoc::where([
            'user_id' => auth()->user()->id,
            'role_name' => 'security',
            'active' => 1
        ]);

        if(!$building->exists()) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => "You don't have access to login to the application!",
                'code' => 403,
            ]))->response()->setStatusCode(403);
        }

    }
}
