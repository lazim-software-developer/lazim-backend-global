<?php

namespace App\Http\Controllers\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Building\BuildingResourceCollection;
use App\Models\Building\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buildings = Building::get(); // Fetch 10 buildings per page. Adjust as needed.
        // $buildings = Building::paginate(10); // Fetch 10 buildings per page. Adjust as needed.
        return new BuildingResourceCollection($buildings);
    }
}
