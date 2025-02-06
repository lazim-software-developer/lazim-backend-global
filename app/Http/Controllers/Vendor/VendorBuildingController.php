<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Http\Request;
use App\Models\Vendor\Vendor;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;

class VendorBuildingController extends Controller
{
    public function listBuildings(Request $request, Vendor $vendor) {
        $buildings = $vendor->buildings->where('pivot.active', true);

        if ($request->type == 'Property Manager') {
            $buildings = $buildings
                ->whereNotNull('pivot.owner_association_id')
                ->filter(function($building) use($request) {
                    return $building->ownerAssociations->contains('role', $request->type);
                });
        } elseif ($request->has('type')) {
            $buildings = $buildings->filter(function($building) use($request) {
                return $building->ownerAssociations->contains('role', $request->type);
            });
        }

        $buildings = $buildings->unique();

        return BuildingResource::collection($buildings);
    }
}
