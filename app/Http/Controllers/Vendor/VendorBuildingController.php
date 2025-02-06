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

        if($request->has('type') && $request->type == 'Property Manager'){
            $buildings = $vendor->buildings->where('pivot.active', true)
                ->whereNotNull('pivote.owner_association_id')
                ->unique();
        }else{
            $buildings = $vendor->buildings->where('pivot.active', true)
                ->unique();
        }
        if($request->type == 'Property Manager'){
            Log::info($buildings->pluck('name'));
        }

        if ($request->has('type')) {
            $buildings = $buildings->filter(function($buildings) use($request){
                return $buildings->ownerAssociations->contains('role',$request->type);
            });
        }
        if($request->type == 'Property Manager'){
            Log::info($buildings->pluck('name'));
        }

        return BuildingResource::collection($buildings);
    }
}
