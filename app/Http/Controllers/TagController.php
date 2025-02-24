<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Master\Service;
use App\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Service::where('active', true)->get();
        
        return TagResource::collection($tags);
    }
}
