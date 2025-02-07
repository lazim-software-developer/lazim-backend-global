<?php
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;
use App\Models\Building\Building;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorBuildingController extends Controller
{
    public function listBuildings(Request $request, Vendor $vendor)
    {
        if ($request->has('type') && $request->type == 'Property Manager') {
            $buildingIds = DB::table('building_vendor')
                ->where(['vendor_id' => $vendor->id, 'building_vendor.active' => true])
                ->whereNotNull('building_vendor.owner_association_id')
                ->join('owner_associations', 'building_vendor.owner_association_id', '=', 'owner_associations.id')
                ->where('owner_associations.role', 'Property Manager')
                ->select('building_vendor.building_id')
                ->pluck('building_id');
        } else {
            $buildingIds = DB::table('building_vendor')
                ->where(['vendor_id' => $vendor->id, 'active' => true])
                ->select('building_vendor.building_id')
                ->pluck('building_id');
        }

        $buildings = Building::whereIn('id', $buildingIds)->get();

        if ($request->has('type')) {
            $buildings = $buildings->filter(function ($building) use ($request) {
                return $building->ownerAssociations->contains('role', $request->type);
            });
        }

        return BuildingResource::collection($buildings);
    }
}
