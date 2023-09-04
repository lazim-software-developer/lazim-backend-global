<?php

namespace App\Http\Controllers;

use App\Http\Resources\OaServiceRequestResource;
use App\Http\Resources\ServiceParameterResource;
use App\Imports\AssetImport;
use App\Imports\ByMethodImport;
use App\Imports\EquityImport;
use App\Imports\ExpenseBudgetImport;
use App\Imports\ExpenseGeneralImport;
use App\Imports\ExpenseImport;
use App\Imports\ExpenseReservedImport;
use App\Imports\IncomeBudgetImport;
use App\Imports\IncomeGeneralImport;
use App\Imports\IncomeImport;
use App\Imports\IncomeReservedImport;
use App\Imports\LiabilityImport;
use App\Imports\RecoveryImport;
use App\Imports\TestImport;
use App\Models\OaServiceRequest;
use App\Models\ServiceParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use \stdClass;

class TestController extends Controller
{
    public function uploadEservices(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->eservices       = $serviceData;
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->bankBalance     = $bankBalance;
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->utilityExpenses = [];
        $data->collection      = $collection;
        $data->delinquents     = [];

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);

        return $body = $response->body();

    }

    public function uploadHappinessCenter(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->eservices       = [];
        $data->happinessCenter = $serviceData;
        $data->balanceSheet    = $balanceSheet;
        $data->bankBalance     = $bankBalance;
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->utilityExpenses = [];
        $data->collection      = $collection;
        $data->delinquents     = [];

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);

        return $body = $response->body();

    }
    public function uploadAccountsPayable(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];

        $data = new stdClass();

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->bankBalance     = $bankBalance;
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->accountsPayable = $serviceData;
        $data->workOrders      = [];
        $data->assets          = [];
        $data->utilityExpenses = [];
        $data->collection      = $collection;
        $data->delinquents     = [];

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
        ])->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }
    public function uploadWorkOrders(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->workOrders      = $serviceData;
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->assets          = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->delinquents     = [];
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }
    public function uploadAssets(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->assets          = $serviceData;
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $generalFund;
        $data->delinquents     = [];
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();
    }
    public function uploadDelinquents(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $serviceData = Excel::toArray(new TestImport, $request->file('file'))[0];
        $data        = new stdClass();

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->delinquents     = $serviceData;
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();
    }

    public function uploadBalanceSheet(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $income    = Excel::toArray(new IncomeImport, $request->file('file'))[0];
        $expense   = Excel::toArray(new ExpenseImport, $request->file('file'))[1];
        $asset     = Excel::toArray(new AssetImport, $request->file('file'))[2];
        $liability = Excel::toArray(new LiabilityImport, $request->file('file'))[3];
        $equity    = Excel::toArray(new EquityImport, $request->file('file'))[4];
        $data      = new stdClass();

        $balanceSheet = new stdClass;

        $balanceSheet->income    = $income;
        $balanceSheet->expense   = $expense;
        $balanceSheet->asset     = $asset;
        $balanceSheet->liability = $liability;
        $balanceSheet->equity    = $equity;

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->delinquents     = [];
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }

    public function uploadReservedFund(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $income  = Excel::toArray(new IncomeReservedImport, $request->file('file'))[0];
        $expense = Excel::toArray(new ExpenseReservedImport, $request->file('file'))[1];

        $data = new stdClass();

        $reservedFund = new stdClass;

        $reservedFund->income  = $income;
        $reservedFund->expense = $expense;

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->delinquents     = [];
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }

    public function uploadBudgetVsActual(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $income_accounts  = Excel::toArray(new IncomeBudgetImport, $request->file('file'))[0];
        $expense_accounts = Excel::toArray(new ExpenseBudgetImport, $request->file('file'))[1];

        $data = new stdClass();

        $budgetVsActual = new stdClass;

        $budgetVsActual->income  = $income_accounts;
        $budgetVsActual->expense = $expense_accounts;

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->delinquents     = [];
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }
    public function uploadGeneralFund(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

        $income  = Excel::toArray(new IncomeGeneralImport, $request->file('file'))[0];
        $expense = Excel::toArray(new ExpenseGeneralImport, $request->file('file'))[1];

        $data = new stdClass();

        $generalFund = new stdClass;

        $generalFund->income  = $income;
        $generalFund->expense = $income;

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = [];
        $collection->recovery  = new stdClass;

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->delinquents     = [];
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }
    // Collection
    
    public function uploadCollection(Request $request)
    {
        // $store = OaServiceRequest::create($request->all());
        OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);

       $recovery  = Excel::toArray(new RecoveryImport, $request->file('file'))[0];
       $byMethod = Excel::toArray(new ByMethodImport, $request->file('file'))[1];

        $data = new stdClass();

        $generalFund = new stdClass;

        $generalFund->income  = [];
        $generalFund->expense = [];

        $balanceSheet = new stdClass;

        $balanceSheet->income    = [];
        $balanceSheet->expense   = [];
        $balanceSheet->asset     = [];
        $balanceSheet->liability = [];
        $balanceSheet->equity    = [];

        $bankBalance = new stdClass;

        $bankBalance->statement = new stdClass;
        $bankBalance->bankbook  = new stdClass;

        $budgetVsActual = new stdClass;

        $budgetVsActual->expense_accounts = [];
        $budgetVsActual->income_accounts  = [];

        $reservedFund = new stdClass;

        $reservedFund->income  = [];
        $reservedFund->expense = [];

        $collection = new stdClass;

        $collection->by_method = $byMethod;
        $collection->recovery  = $recovery[0];

        $data->property_group = $request->property_group;
        $data->from_date        = $request->from_date;
        $data->to_date          = $request->to_date;
        $data->delinquents     = [];
        $data->eservices       = [];
        $data->happinessCenter = [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = [];
        $data->workOrders      = [];
        $data->assets          = [];
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
            // 'Authorization' => 'Bearer ' . $bearerToken, // Assuming you have $bearerToken variable with the actual token value
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }

    public function uploadAll(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group' => $request->property_group,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'status' => 'Posted',
            'uploaded_by' => 1,
        ]);
        
        if ($request->has('e_services')) {
            $e_services = Excel::toArray(new TestImport, $request->file('e_services'))[0];
        }
        // if ($request->has('happiness_center')) {
        //     $happiness_center = Excel::toArray(new TestImport, $request->file('happiness_center'))[0];
        // }
        if ($request->has('balance_sheet')) {
            $income_balance    = Excel::toArray(new IncomeImport, $request->file('balance_sheet'))[0];
            $expense_balance   = Excel::toArray(new ExpenseImport, $request->file('balance_sheet'))[1];
            $asset_balance     = Excel::toArray(new AssetImport, $request->file('balance_sheet'))[2];
            $liability_balance = Excel::toArray(new LiabilityImport, $request->file('balance_sheet'))[3];
            $equity_balance    = Excel::toArray(new EquityImport, $request->file('balance_sheet'))[4];
        } 
        if ($request->has('accounts_payables')) {
            $accounts_payables = Excel::toArray(new TestImport, $request->file('accounts_payables'))[0];
        }
        if ($request->has('delinquents')) {
            $delinquents = Excel::toArray(new TestImport, $request->file('delinquents'))[0];
        }
        if ($request->has('work_orders')) {
            $work_orders = Excel::toArray(new TestImport, $request->file('work_orders'))[0];
        }
        if ($request->has('reserve_fund')) {
            $income_reserved  = Excel::toArray(new IncomeReservedImport, $request->file('reserve_fund'))[0];
            $expense_reserved = Excel::toArray(new ExpenseReservedImport, $request->file('reserve_fund'))[1];
        }
        if ($request->has('budget_vs_actual')) {
            $income_accounts  = Excel::toArray(new IncomeBudgetImport, $request->file('budget_vs_actual'))[0];
            $expense_accounts = Excel::toArray(new ExpenseBudgetImport, $request->file('budget_vs_actual'))[1];
        }
        if ($request->has('central_fund_statement')) {
            $income = Excel::toArray(new IncomeGeneralImport, $request->file('central-fund-statement'))[0];
            $expense = Excel::toArray(new ExpenseGeneralImport, $request->file('central-fund-statement'))[1];
        }
        if ($request->has('collections')) {
            $recovery = Excel::toArray(new RecoveryImport, $request->file('collections'))[0];
            $byMethod = Excel::toArray(new ByMethodImport, $request->file('collections'))[1];
        }
        
    //    $recovery  = Excel::toArray(new RecoveryImport, $request->file(' '))[0];
    //    $byMethod = Excel::toArray(new ByMethodImport, $request->file('file'))[1];

    $data = new stdClass();

    $generalFund = new stdClass;

    $generalFund->income  =  $request->has('central_fund_statement') ? $income : [];
    $generalFund->expense = $request->has('central_fund_statement') ? $expense : [];

    $balanceSheet = new stdClass;

    $balanceSheet->income    = $request->has('balance_sheet') ? $income_balance : [];
    $balanceSheet->expense   = $request->has('balance_sheet') ? $expense_balance : [];
    $balanceSheet->asset     = $request->has('balance_sheet') ? $asset_balance : [];
    $balanceSheet->liability = $request->has('balance_sheet') ? $liability_balance : [];
    $balanceSheet->equity    = $request->has('balance_sheet') ? $equity_balance : [];

    $bankBalance = new stdClass;

    $bankBalance->statement = new stdClass;
    $bankBalance->bankbook  = new stdClass;

    $budgetVsActual = new stdClass;

    $budgetVsActual->expense_accounts = $request->has('budget_vs_actual') ? $income_accounts : [];
    $budgetVsActual->income_accounts  = $request->has('budget_vs_actual') ? $expense_accounts : [];

    $reservedFund = new stdClass;

    $reservedFund->income  = $request->has('reserve_fund') ? $income_reserved : [];
    $reservedFund->expense = $request->has('reserve_fund') ? $expense_reserved : [];

    $collection = new stdClass;

    $collection->by_method = $request->has('collections') ? $byMethod : [];
    $collection->recovery  = $request->has('collections') ? $recovery[0] : [];

    $data->propertyGroupId = $request->property_group;
    $data->fromDate        = $request->from_date;
    $data->toDate          = $request->to_date;
    $data->delinquents     = [];
    $data->eservices       = $request->has('e_services') ? $e_services : [];
    $data->happinessCenter = [];
    // $data->balanceSheet    = $request->has('balance_sheet') ? $balance_sheet : $balanceSheet;
    $data->balanceSheet    = $balanceSheet;
    $data->accountsPayable = $request->has('accounts_payables') ? $accounts_payables : [];
    $data->workOrders      = $request->has('work_orders') ? $work_orders : [];
    $data->assets          = [];
    $data->bankBalance     = $bankBalance;
    $data->utilityExpenses = [];
    $data->budgetVsActual  = $budgetVsActual;
    $data->generalFund     = $generalFund;
    $data->reservedFund    = $reservedFund;
    $data->collection      = $collection;

        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
        ])
            ->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
        return $body = $response->body();

    }
    public function serviceParameters()
    {
        return ServiceParameterResource::collection(ServiceParameter::all());
    }
    public function serviceRequest()
    {
        return OaServiceRequestResource::collection(OaServiceRequest::all());
    }
}
