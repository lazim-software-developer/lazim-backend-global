<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\FlatOwnerResource;
use App\Http\Resources\Building\FlatResource;
use App\Http\Resources\User\UserResource;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlatController extends Controller
{
    public function fetchFlats(Building $building, Request $request)
    {
        $flats = $building->flats()->with('ownerAssociation')->get();

        if (!$request->has('type')) {
            return FlatResource::collection($flats);
        }

        $pmFlats = DB::table('property_manager_flats')
            ->whereIn('flat_id', $flats->pluck('id'))
            ->where('active', true)
            ->pluck('flat_id');

        if ($request->type == 'Property Manager') {
            $flats = Flat::whereIn('id', $pmFlats)->with('ownerAssociation')->get();
        } elseif ($request->type == 'OA') {
            $flats = $flats->whereNotIn('id', $pmFlats);
        }

        return FlatResource::collection($flats);
    }

    // List all flat owners
    public function fetchFlatOwners(Flat $flat) {
        if($flat && $flat->owners->count() > 0) {
            return FlatOwnerResource::collection($flat->owners);
        }
        return UserResource::collection(collect([auth()->user()]));
    }
}
