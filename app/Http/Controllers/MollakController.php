<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Resources\Master\PropertyGroupResource;
use App\Http\Resources\Master\ServicePeriodResource;
use App\Http\Resources\Master\UnitResource;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function test()
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/owners/235553");

        return $data = $response->json();
    }

    public function sendSMS(Request $request)
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
        ])->post(env("SMS_LINK") . "otpgenerate?username=" . env("SMS_USERNAME") . "&password=" . env("SMS_PASSWORD") . "&msisdn=" . $request->phone . "&msg=Your%20one%20time%20OTP%20is%20%25m&source=ILAJ-LAZIM&tagname=" . env("SMS_TAG") . "&otplen=5&exptime=60");

        return $response;
    }

    public function verifyOTP(Request $request)
    {
        $otp = $request->otp;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
        ])->post(env("SMS_LINK") . "checkotp?username=" . env("SMS_USERNAME") . "&password=" . env("SMS_PASSWORD") . "&msisdn=" . $request->phone . "&otp=" . $otp);

        if ($response->successful()) {
            $value = $response->json();

            if ($value == 101) {
                return response()->json([
                    'message' => 'Phone successfully verified.',
                    'status' => 'success'
                ], 200);
            }
            return response()->json([
                'message' => 'We were unable to verify your phone number. Please try again!',
                'status' => 'error'
            ], 400);
        } else {
            return response()->json([
                'message' => 'We were unable to verify your phone number. Please try again!',
                'status' => 'error'
            ], 400);
        }

        // TODO: ERROR HANDLING
        // if ($response == 101) {
        //     User::where('phone', $request->phone)->update(['phone_verified' => true]);

        //     return response()->json([
        //         'message' => 'Phone successfully verified.',
        //         'status' => 'success'
        //     ], 200);
        // }
        // return response()->json([
        //     'message' => 'We were unable to verify your phone number. Please try again!',
        //     'status' => 'error'
        // ], 400);
    }
}
