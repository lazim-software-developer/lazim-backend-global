<?php

namespace App\Http\Controllers;

use App\Imports\TestImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use \stdClass;

class TestController extends Controller
{
    public function uploadEservices(Request $request)
    {
        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
        $data->eservices       = $serviceData;
        $data->happinessCenter = [];
        $data->balanceSheet    = [];
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = [];
        $data->utilityExpenses = [];
        $data->budgetVsActual  = [];
        $data->generalFund     = [];
        $data->reservedFund    = [];
        $data->delinquents     = [];
        $data->collection      = [];
        return $data;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', [

            ]);

        return $body = $response->body();
    }

    public function uploadHappinessCenter(Request $request)
    {
        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
        $data->happinessCenter = $serviceData;
        $data->eservices       = [];
        $data->balanceSheet    = [];
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = [];
        $data->utilityExpenses = [];
        $data->budgetVsActual  = [];
        $data->generalFund     = [];
        $data->reservedFund    = [];
        $data->delinquents     = [];
        $data->collection      = [];
        return $data;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', [

            ]);
        return $body = $response->body();

    }
    public function uploadAccountsPayable(Request $request)
    {

        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
        $data->accountsPayable = $serviceData;

        $data->happinessCenter =[];
        $data->eservices       = [];
        $data->balanceSheet    = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = [];
        $data->utilityExpenses = [];
        $data->budgetVsActual  = [];
        $data->generalFund     = [];
        $data->reservedFund    = [];
        $data->delinquents     = [];
        $data->collection      = [];
        return $data;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', [

            ]);
        return $body = $response->body();

    }
    public function uploadWorkOrders(Request $request)
    {
        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
        $data->workOrders      = $serviceData;
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = [];
        $data->accountsPayable = [];
        $data->assets          = [];
        $data->bankBalance     = [];
        $data->utilityExpenses = [];
        $data->budgetVsActual  = [];
        $data->generalFund     = [];
        $data->reservedFund    = [];
        $data->delinquents     = [];
        $data->collection      = [];
        return $data;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', [

            ]);
        return $body = $response->body();

    }
    public function uploadAssets(Request $request)
    {
        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
        $data->assets          = $serviceData;
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = [];
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->bankBalance     = [];
        $data->utilityExpenses = [];
        $data->budgetVsActual  = [];
        $data->generalFund     = [];
        $data->reservedFund    = [];
        $data->delinquents     = [];
        $data->collection      = [];
        return $data;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', [

            ]);
        return $body = $response->body();
    }
    public function uploadDelinquents(Request $request)
    {
        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
        $data->delinquents     = $serviceData;
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = [];
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = [];
        $data->utilityExpenses = [];
        $data->budgetVsActual  = [];
        $data->generalFund     = [];
        $data->reservedFund    = [];
        $data->collection      = [];
        return $data;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', [

            ]);
        return $body = $response->body();
    }

}
