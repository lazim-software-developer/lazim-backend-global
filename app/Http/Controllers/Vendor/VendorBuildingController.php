<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;

class VendorBuildingController extends Controller
{
    public function listBuildings(Request $request,Vendor $vendor){

        $buildings = $vendor->buildings->where('pivot.active', true)->where('pivot.end_date','>',now()->toDateString())->unique();

        if ($request->has('filter') && $request->input('filter') == true) {
            $buildings = $vendor->buildings->unique();
        }
        if ($request->has('type')) {
            $buildings = $buildings->filter(function($buildings) use($request){
                return $buildings->ownerAssociations->contains('role',$request->type);
            });
        }

        return BuildingResource::collection($buildings);
    }
}
