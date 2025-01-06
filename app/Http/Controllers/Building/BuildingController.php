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
            $query->whereHas('ownerAssociations', function($q) {
                $q->where('active', 1);
            });
        }

        $buildings = $query->get();
        return BuildingResource::collection($buildings);
    }
}
