<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MollakController extends Controller
{
    public function getProperties($oaId) {
        return $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
        ])->get(env("MOLLAK_API_URL")."/managementcompany/".$oaId."/propertygroups/");
    }
}
