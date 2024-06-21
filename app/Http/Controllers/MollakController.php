<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Models\Master\Service;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use App\Models\Accounting\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Accounting\Budgetitem;
use App\Models\Accounting\SubCategory;
use App\Http\Resources\Master\UnitResource;
use App\Http\Resources\Master\PropertyGroupResource;
use App\Http\Resources\Master\ServicePeriodResource;

class MollakController extends Controller
{
    public function fetchPropertyGroups()
    {
        $oaId = auth()->user()->ownerAssociation->mollak_id;

        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/managementcompany/" . $oaId . "/propertygroups");
        // ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/managementcompany/" . 54713 . "/propertygroups");

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
        // ])->get(env("MOLLAK_API_URL") . "/sync/invoices/" . $propertyId . "/servicechargeperiods");
        ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/invoices/" . $propertyId . "/servicechargeperiods");

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

    // API to check if the Mollak APIs are working. Sometimes the APIs are not working.
    // This is the helper function to check that
    public function test()
    {
        // $response = Http::withoutVerifying()->withHeaders([
        //     'content-type' => 'application/json',
        //     'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        // ])->get(env("MOLLAK_API_URL") . '/sync/managementcompany');
        // $response = Http::withOptions(['verify' => false])->withHeaders([
        //     'content-type' => 'application/json',
        //     'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        // ])->get(env("MOLLAK_API_URL") . "/sync/propertygroups/" . "235553" . "/units");

        // $response = Http::withoutVerifying()->withHeaders([
        //     'content-type' => 'application/json',
        //     'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        // ])->get(env("MOLLAK_API_URL") . '/sync/invoices/' . "235553" . '/all/' . "Q1-JAN2023-DEC2023");
        // $response = Http::withoutVerifying()->withHeaders([
        //         'content-type' => 'application/json',
        //         'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        //     ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/owners/235553");

        // $results = Http::withOptions(['verify' => false])->withHeaders([
        //         'content-type' => 'application/json',
        //         'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        //     ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/managementcompany/" . 54713 . "/propertygroups");

        $results = Http::withOptions(['verify' => false])->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/invoices/" . 235553 . "/servicechargeperiods");
    
            // Decode the API response
            $data = $results->json();
    
            // Return the transformed data using the API resource
            // return PropertyGroupResource::collection($data['response']['propertyGroups']);
            return ServicePeriodResource::collection($data['response']['serviceChargePeriod']);

        // $response = Http::withoutVerifying()->withHeaders([
        //     'Content-Type' => 'application/json',
        //     'consumer-id' => env("MOLLAK_CONSUMER_ID"),
        // ])->post("https://qagate.dubailand.gov.ae/mollak/external/sync", [
        //     'timeStamp' => '2019-07-25T17:11:38.036044+04:00',
        //     'syncType' => 'ownership_changed',
        //     'parameters' => [
        //         ['key' => 'propertyGroupId', 'value' => 235553],
        //     ],
        // ]);

        // LOG::info("MOLLA ". $response);

        // return $data = $response->json();
    }

    public function sendSMS(Request $request)
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
        ])->post(env("SMS_LINK") . "otpgenerate?username=" . env("SMS_USERNAME") . "&password=" . env("SMS_PASSWORD") . "&msisdn=" . $request->phone . "&msg=Your%20one%20time%20OTP%20is%20%25m&source=ILAJ-LAZIM&tagname=" . env("SMS_TAG") . "&otplen=5&exptime=60");

        // Log::info('RESPONSEEE:-' . $response);

        return $response;
    }

    public function verifyOTP(Request $request)
    {
        $otp = $request->otp;

        if(env('APP_ENV') == 'production'){
            $response = Http::withOptions(['verify' => false])->withHeaders([
                'content-type' => 'application/json',
            ])->post(env("SMS_LINK") . "checkotp?username=" . env("SMS_USERNAME") . "&password=" . env("SMS_PASSWORD") . "&msisdn=" . $request->phone . "&otp=" . $otp);
    
                if ($response->successful()) {
                        $value = $response->json();
    
                        if ($value == 101) {
                            User::where('phone', $request->phone)->update(['phone_verified' => true]);
    
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
        }
        else{
            User::where('phone', $request->phone)->update(['phone_verified' => true]);
            return response()->json([
                        'message' => 'Phone successfully verified.',
                        'status' => 'success'
                    ], 200);
        }

    }

    public function fetchbudget(Request $request)
    {
        $propertygroupId = $request->propertyGroupId;
        $dateRange = "JAN" . date("Y") . "-DEC" . date("Y");
        $building = Building::where('property_group_id', $propertygroupId)->first();
        //validate building exists or not
        if ($building == null) {
            return response()->json(['message' => 'No building data available for the propertyGroupId'], 400);
        }
        //validate if a budget already exists for building
        [$start, $end] = explode('-', $dateRange);
        $startDate = Carbon::createFromFormat('M Y', $start)->startOfMonth();
        $endDate = Carbon::createFromFormat('M Y', $end)->endOfMonth();
        // Check if budget exists for the given period
        $existingBudget = Budget::where('building_id', $building->id)
            ->where('budget_from', $startDate->toDateString())
            ->where('budget_to', $endDate->toDateString())
            ->first();

        if ($existingBudget) {
            return response()->json(['message' => 'A budget for the specified period and building already exists.'], 400);
        }

        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/budgets/" . $propertygroupId . "/all" . "/" . $dateRange);

        $data = $results->json(); // Decode the API response

        // Error handling for unexpected structure
        if (!isset($data['response']['serviceChargeGroups'])) {
            return response()->json(['error' => 'Unexpected API response structure'], 400);
        }

        // Check if serviceChargeGroups is empty
        if (empty($data['response']['serviceChargeGroups'])) {
            return response()->json(['message' => 'No data available for the current budget period year'], 400);
        }

        $serviceChargeGroups = $data['response']['serviceChargeGroups'];

        foreach ($serviceChargeGroups as $serviceChargeGroup) {
            // Accessing the budget period details
            $budgetPeriodCode = $serviceChargeGroup['budgetPeriodCode'];
            $budgetPeriodFrom = $serviceChargeGroup['budgetPeriodFrom'];
            $budgetPeriodTo = $serviceChargeGroup['budgetPeriodTo'];


            $budget = Budget::firstOrCreate([
                'building_id' => $building->id,
                'owner_association_id' => $building->owner_association_id,
                'budget_period' => $budgetPeriodCode,
                'budget_from' => $budgetPeriodFrom,
                'budget_to' => $budgetPeriodTo,
            ]);
            // Log::info('Budget created:-' . $budget);

            // Accessing the budget items
            $budgetItems = $serviceChargeGroup['budgetItems'];

            foreach ($budgetItems as $item) {
                $category = Category::firstOrCreate([
                    'name' => $item['categoryName']['englishName'],
                    'code' => $item['categoryCode'],
                ]);

                $subcategory = SubCategory::firstOrCreate([
                    'name' => $item['subCategoryName']['englishName'],
                    'code' => $item['subCategoryCode'],
                    'category_id' => $category->id,
                ]);

                $service = Service::firstOrCreate([
                    'name' => $item['serviceName']['englishName'],
                    'type' => 'vendor_service',
                    'code' => $item['serviceCode'],
                    'active' => true,
                    'subcategory_id' => $subcategory->id,
                ]);

                if ($service) {
                    $budgetitem = Budgetitem::create([
                        'budget_id' => $budget->id,
                        'service_id' => $service->id,
                        'budget_excl_vat' => $item['totalCost'],
                        'vat_rate' => 0.05,
                        'vat_amount' => $item['vatAmount'],
                        'total' => $item['totalCost'] + $item['vatAmount'],
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Budgets processed successfully'], 200);
    }

    public function ServicePeriods($propertyId)
    {
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/invoices/" . $propertyId . "/servicechargeperiods");

        // Assuming the API returns a JSON response, we'll decode it
        $data = $results->json();

        // Return the transformed data using the API resource
        return ServicePeriodResource::collection($data['response']['serviceChargePeriod']); // Adjust the key as per the actual response structure
    }

    public function testing(){
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://b2bgateway.dubailand.gov.ae/mollak/external/sync/managementcompany");

        return $data = $results->json();
    }

    public function webhook(Request $request){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
        // header("Content-Type: application/json");
        // header("Access-Control-Allow-Headers: Content-Type, Authorization");
        Log::info($request->header('x-api-key'));
        Log::info($request->headers->all());
        Log::info("Webhook--->".json_encode($request->all()));
        return [
            'isExecuted' => true,
            'acknowledgeRef' => random_int(111111,999999)
        ];
    }
}
