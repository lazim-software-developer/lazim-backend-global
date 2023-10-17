<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\FlatResource;
use App\Models\Building\Building;

class FlatController extends Controller
{
    public function fetchFlats(Building $building)
    {
        // $flats = $building->flats()->paginate(10);
        $flats = $building->flats()->get(10);
        return FlatResource::collection($flats);
    }
}
