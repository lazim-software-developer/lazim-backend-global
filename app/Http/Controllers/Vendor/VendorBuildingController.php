<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;

class VendorBuildingController extends Controller
{
    public function listBuildings(Request $request,Vendor $vendor){

        $buildings = $vendor->buildings->where('pivot.active', true)->where('pivot.end_date','>',now()->toDateString())->unique();

        if ($request->has('filter') && $request->input('filter') == true) {
            $buildings = $vendor->buildings->unique();
        }

        return BuildingResource::collection($buildings);
    }
}
