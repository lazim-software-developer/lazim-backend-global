<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\FlatOwnerResource;
use App\Http\Resources\Building\FlatResource;
use App\Http\Resources\User\UserResource;
use App\Models\Building\Building;
use App\Models\Building\Flat;

class FlatController extends Controller
{
    public function fetchFlats(Building $building)
    {
        // $flats = $building->flats()->paginate(10);
        $flats = $building->flats()->with('ownerAssociation')->get();
        return FlatResource::collection($flats);
    }

    // List all flat owners
    public function fetchFlatOwners(Flat $flat) {
        // Check if flat exists
        if($flat) {
            if($flat->owners) {
                return FlatOwnerResource::collection($flat->owners);
            }
            $owner = auth()->user();
            return new UserResource($owner);
        }
    }
}
