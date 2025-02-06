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

        if($request->has('type') && $request->type == 'OA'){
            $buildings = $vendor->buildings->where('pivot.active', true)
            ->unique();
        }else{
            $buildings = $vendor->buildings->where('pivot.active', true)
                ->whereNotNull('pivote.owner_association_id')
                ->unique();
        }
        Log::info($buildings->pluck('name'));

        if ($request->has('filter') && $request->input('filter') == true) {
            $buildings = $vendor->buildings->unique();
        }
        if ($request->has('type')) {
            $buildings = $buildings->filter(function($buildings) use($request){
                return $buildings->ownerAssociations->contains('role',$request->type);
            });
        }
        Log::info($buildings->pluck('name'));

        return BuildingResource::collection($buildings);
    }
}
