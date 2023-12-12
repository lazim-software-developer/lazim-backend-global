<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Resources\Community\AboutCommunityResource;
use App\Models\Building\Building;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function about(Building $Building)
    {
        return new AboutCommunityResource($Building);
    }
}
