<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;
use App\Http\Resources\Building\BuildingResourceCollection;
use App\Models\Building\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Building::query();

        if ($request->has('type')) {
            $query->whereHas('ownerAssociations', function($q) use ($request) {
                $q->where('role', $request->type);
            });
        }
        if($request->registration){
            $activeBuildings = DB::table('building_owner_association')
                ->where('building_id', $query->pluck('id'))
                ->where('active', 1)
                ->pluck('building_id');
            $query = Building::whereIn('id',$activeBuildings);
        }

        $buildings = $query->get();
        return BuildingResource::collection($buildings);
    }
}
