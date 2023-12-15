<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;
use App\Models\Vendor\Vendor;

class VendorBuildingController extends Controller
{
    public function listBuildings(Vendor $vendor){

        
        $buildings = $vendor->buildings->where('pivot.active', true)->where('pivot.end_date','>',now()->toDateString())->unique();

        return BuildingResource::collection($buildings);
    }
}
