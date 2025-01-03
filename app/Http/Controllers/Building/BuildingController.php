<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResource;
use App\Http\Resources\Building\BuildingResourceCollection;
use App\Models\Building\Building;
use Illuminate\Http\Request;

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

        $buildings = $query->get();
        return BuildingResource::collection($buildings);
    }
}
