<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Resources\Assets\TechnicianAssetResource;
use App\Models\TechnicianAssets;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index() {
        $assets = TechnicianAssets::where(['technician_id' => auth()->user()->id, 'active' =>1])->get();
        
        return TechnicianAssetResource::collection($assets);
    }
}
