<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;
use App\Http\Resources\Building\BuildingResourceCollection;
use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Building::query();

        if ($request->has('type')) {
            $oaPm = OwnerAssociation::where('role',$request->type)->pluck('id');
            $oaPmBuildings = DB::table('building_owner_association')
                ->whereIn('building_id', $query->pluck('id'))
                ->whereIn('owner_association_id', $oaPm)
                ->where('active', true)
                ->pluck('building_id');
            $query = Building::whereIn('id',$oaPmBuildings);

        }
        Log::info($query->pluck('id')->toArray());
        Log::info($query->toSql());
        Log::info($query->pluck('id')->toArray());

        if($request->registration){
            $activeBuildings = DB::table('building_owner_association')
                ->whereIn('building_id', $query->pluck('id'))
                ->where('active', true)
                ->pluck('building_id');
            $query = Building::whereIn('id',$activeBuildings);
        }

        $buildings = $query->get();
        return BuildingResource::collection($buildings);
    }
}
