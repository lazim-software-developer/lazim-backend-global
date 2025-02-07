<?php
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;
use App\Models\OwnerAssociation;
use App\Models\Vendor\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorBuildingController extends Controller
{
    public function listBuildings(Request $request, Vendor $vendor)
    {
        if ($request->has('type') && $request->type == 'Property Manager') {
            $buildings = $vendor->buildings()
                ->wherePivot('active', true)
                ->wherePivotNotNull('owner_association_id')
                ->get()
                ->filter(function ($building) {
                    $ownerAssociation = OwnerAssociation::find($building->pivot->owner_association_id);
                    Log::info($ownerAssociation);
                    return $ownerAssociation && $ownerAssociation->role === 'Property Manager';
                })
                ->unique();
        } else {
            $buildings = $vendor->buildings->where('pivot.active', true)
                ->unique();
        }

        if ($request->has('type')) {
            $buildings = $buildings->filter(function ($buildings) use ($request) {
                return $buildings->ownerAssociations->contains('role', $request->type);
            });
        }

        return BuildingResource::collection($buildings);
    }
}
