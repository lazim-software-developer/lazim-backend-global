<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Resources\Master\PropertyGroupResource;
use App\Http\Resources\Master\ServicePeriodResource;
use App\Http\Resources\Master\UnitResource;

class MollakController extends Controller
{
    public function fetchPropertyGroups()
    {
        $oaId = auth()->user()->ownerAssociation->mollak_id;
        
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/managementcompany/" . $oaId . "/propertygroups");

        // Decode the API response
        $data = $results->json();

        // Return the transformed data using the API resource
        return PropertyGroupResource::collection($data['response']['propertyGroups']);
    }

    // Get all service period for a given property id
    public function fetchServicePeriods($propertyId)
    {
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/invoices/" . $propertyId . "/servicechargeperiods");

        // Assuming the API returns a JSON response, we'll decode it
        $data = $results->json();

        // Return the transformed data using the API resource
        return ServicePeriodResource::collection($data['response']['serviceChargePeriod']); // Adjust the key as per the actual response structure
    }

    // Get all Units for a given propery
    public function fetchUnits($propertyGroupId)
    {
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/propertygroups/" . $propertyGroupId . "/units");
    
        // Assuming the API returns a JSON response, we'll decode it
        $data = $results->json();
    
        // Return the transformed data using the API resource
        return UnitResource::collection($data['response']['units']);
    }

    public function test() {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/owners/235553");

        return $data = $response->json();
    }

    public function sendSMS()
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
        ])->post("https://sms.rmlconnect.net/OtpApi/otpgenerate?username=LazimTrans&password=Lazim@10&msisdn=971526006235&msg=Your%20one%20time%20OTP%20is%20%25m&source=Lazim&tagname=Lazim&otplen=5&exptime=60");

        return $data = $response->json();
    }
}
