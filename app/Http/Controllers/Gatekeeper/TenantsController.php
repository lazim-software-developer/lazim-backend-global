<?php

namespace App\Http\Controllers\Gatekeeper;

use App\Http\Controllers\Controller;
use App\Http\Resources\GateKeeper\TenantResource;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use Illuminate\Http\Request;

class TenantsController extends Controller
{
    public function fetchAllTenants(Request $request, Building $building)
    {

        $tenantsQuery = FlatTenant::where(['building_id' => $building->id, 'active' => 1]);

        if ($request->has('unit')) {
            $unit = $request->input('unit');
            $flatId = Flat::where('property_number', 'LIKE', '%' . $unit . '%')->get(['id']);
            $tenantsQuery->whereIn('flat_id', $flatId);
        }

        return TenantResource::collection($tenantsQuery->paginate(10));
    }
}
