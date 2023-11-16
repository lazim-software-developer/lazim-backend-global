<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;

class BuildingController extends Controller
{
    public function index() {
        $user = auth()->user(); // Get the logged-in user
        
        // Assuming the user has a 'technicianVendors' relationship
        $buildings = $user->technicianVendors()
            ->with('vendor.buildings')
            ->get()
            ->pluck('vendor.buildings')
            ->collapse()
            ->unique('id');

        return BuildingResource::collection($buildings);
    }
}
