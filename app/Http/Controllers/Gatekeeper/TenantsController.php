<?php

namespace App\Http\Controllers\GateKeeper;

use App\Http\Controllers\Controller;
use App\Http\Resources\GateKeeper\TenantResource;
use Illuminate\Http\Request;
use App\Models\Building\FlatTenant;
use App\Models\Building\Flat;

use App\Models\Building\Building;

class TenantsController extends Controller
{
    function fetchAllTenants(Request $request, Building $building) {

        $tenantsQuery = FlatTenant::where(['building_id' => $building->id, 'active' => 1]);

        if($request->has('unit')) {
            $unit = $request->input('unit');
            $flatId = Flat::where('property_number','LIKE','%'.$unit.'%')->get(['id']);
            $tenantsQuery->whereIn('flat_id', $flatId);
        }

        return TenantResource::collection($tenantsQuery->paginate(10));
    }
}
