<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MollakController extends Controller
{
    public function getProperties($oaId) {
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL")."/sync/managementcompany/".$oaId."/propertygroups");

        return $results;
    }

    // Get all service period for a given property id
    public function getServicePeriod($propertyId){
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL")."/sync/invoices/".$propertyId."/servicechargeperiods");

        return $results;
    }
}