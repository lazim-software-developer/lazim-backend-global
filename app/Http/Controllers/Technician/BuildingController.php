<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;

class BuildingController extends Controller
{
    public function index() {
        $user = auth()->user();

        $vendors = $user->technicianVendors()
            ->with(['vendor.buildings' => function($query) {
                $query->wherePivot('active', 1);
                    //   ->whereHas('ownerAssociations');
            }])
            ->get();

        $buildings = $vendors->flatMap(function($technicianVendor) {
            return $technicianVendor->vendor->buildings;
        })->unique('id');

        return BuildingResource::collection($buildings);
    }
}
