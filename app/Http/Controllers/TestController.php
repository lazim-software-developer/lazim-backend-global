<?php

namespace App\Http\Controllers;

use App\Http\Resources\OaServiceRequestResource;
use App\Http\Resources\ServiceParameterResource;
use App\Imports\AssetsImport;
use App\Imports\TestImport;
use App\Imports\UtilityExpensesImport;
use App\Models\OaServiceRequest;
use App\Models\ServiceParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use \stdClass;

class TestController extends Controller
{
    public function uploadEservices(Request $request)
    {
        $store = OaServiceRequest::create([
            'service_parameter_id' => $request->service_parameter_id,
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
            'property_group'       => $request->property_group,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
        ]);

        $recovery = Excel::toArray(new RecoveryImport, $request->file('file'))[0];
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

        $data->property_group  = $request->property_group;
        $data->from_date       = $request->from_date;
        $data->to_date         = $request->to_date;
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
        $parameters = ServiceParameter::all();

        $folderPath = now()->timestamp;
        if ($request->has('e_services')) {
            $e_services = Excel::toArray(new TestImport, $request->file('e_services'))[0];

            $document = $request->e_services;
            $mimeType = $document->guessExtension();
            $fileName = 'e_services';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('happiness_center')) {
            $happiness_center = Excel::toArray(new TestImport, $request->file('happiness_center'))[0];

            $document = $request->happiness_center;
            $mimeType = $document->guessExtension();
            $fileName = 'happiness_center';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('balance_sheet')) {
            $income_balance    = Excel::toArray(new TestImport, $request->file('balance_sheet'))[0];
            $expense_balance   = Excel::toArray(new TestImport, $request->file('balance_sheet'))[1];
            $asset_balance     = Excel::toArray(new TestImport, $request->file('balance_sheet'))[2];
            $liability_balance = Excel::toArray(new TestImport, $request->file('balance_sheet'))[3];
            $equity_balance    = Excel::toArray(new TestImport, $request->file('balance_sheet'))[4];

            $document = $request->balance_sheet;
            $mimeType = $document->guessExtension();
            $fileName = 'balance_sheet';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('accounts_payables')) {
            $accounts_payables = Excel::toArray(new TestImport, $request->file('accounts_payables'))[0];

            $document = $request->accounts_payables;
            $mimeType = $document->guessExtension();
            $fileName = 'accounts_payables';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('delinquents')) {
            $delinquentsData = Excel::toArray(new TestImport, $request->file('delinquents'))[0];

            $document = $request->delinquents;
            $mimeType = $document->guessExtension();
            $fileName = 'delinquents';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('work_orders')) {
            $work_orders = Excel::toArray(new TestImport, $request->file('work_orders'))[0];

            $document = $request->work_orders;
            $mimeType = $document->guessExtension();
            $fileName = 'work_orders';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('reserve_fund')) {
            $income_reserved  = Excel::toArray(new TestImport, $request->file('reserve_fund'))[0];
            $expense_reserved = Excel::toArray(new TestImport, $request->file('reserve_fund'))[1];

            $document = $request->reserve_fund;
            $mimeType = $document->guessExtension();
            $fileName = 'reserve_fund';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('budget_vs_actual')) {
            $income_accounts  = Excel::toArray(new TestImport, $request->file('budget_vs_actual'))[0];
            $expense_accounts = Excel::toArray(new TestImport, $request->file('budget_vs_actual'))[1];

            $document = $request->budget_vs_actual;
            $mimeType = $document->guessExtension();
            $fileName = 'budget_vs_actual';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('central_fund_statement')) {
            $income  = Excel::toArray(new TestImport, $request->file('central_fund_statement'))[0];
            $expense = Excel::toArray(new TestImport, $request->file('central_fund_statement'))[1];

            $document = $request->central_fund_statement;
            $mimeType = $document->guessExtension();
            $fileName = 'central_fund_statement';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }
        if ($request->has('collections')) {
            $recovery = Excel::toArray(new TestImport, $request->file('collections'))[0];
            $byMethod = Excel::toArray(new TestImport, $request->file('collections'))[1];

            $document = $request->collections;
            $mimeType = $document->guessExtension();
            $fileName = 'collections';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }

        if ($request->has('bank_balance')) {
            $statement = Excel::toArray(new TestImport, $request->file('bank_balance'))[0];
            $bankbook  = Excel::toArray(new TestImport, $request->file('bank_balance'))[1];

            $document = $request->bank_balance;
            $mimeType = $document->guessExtension();
            $fileName = 'bank_balance';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }

        $import = new AssetsImport;

        Excel::import($import, $request->file('asset_list_and_expenses'));
        $assets = $structuredData = $import->getResults();

        if ($request->has('asset_list_and_expenses')) {
            $document = $request->asset_list_and_expenses;
            $mimeType = $document->guessExtension();
            $fileName = 'asset_list_and_expenses';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }

        $uaImport = new UtilityExpensesImport;

        Excel::import($uaImport, $request->file('utility_expenses'));
        $utility = $uaImport->getResults();

        if ($request->has('utility_expenses')) {
            $document = $request->utility_expenses;
            $mimeType = $document->guessExtension();
            $fileName = 'utility_expenses';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        }

        $data = new stdClass();

        $generalFund = new stdClass;

        $generalFund->income  = $request->has('central_fund_statement') ? $income : [];
        $generalFund->expense = $request->has('central_fund_statement') ? $expense : [];

        $balanceSheet = new stdClass;

        $balanceSheet->income    = $request->has('balance_sheet') ? $income_balance : [];
        $balanceSheet->expense   = $request->has('balance_sheet') ? $expense_balance : [];
        $balanceSheet->asset     = $request->has('balance_sheet') ? $asset_balance : [];
        $balanceSheet->liability = $request->has('balance_sheet') ? $liability_balance : [];
        $balanceSheet->equity    = $request->has('balance_sheet') ? $equity_balance : [];

        $bankBalance = new stdClass;

        $bankBalance->statement = $request->has('bank_balance') ? $statement[0] : new stdClass;
        $bankBalance->bankbook  = $request->has('bank_balance') ? $bankbook[0] : new stdClass;

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
        $data->happinessCenter = $request->has('happiness_center') ? $happiness_center : [];
        $data->balanceSheet    = $balanceSheet;
        $data->accountsPayable = $request->has('accounts_payables') ? $accounts_payables : [];
        $data->workOrders      = $request->has('work_orders') ? $work_orders : [];
        $data->assets          = $request->has('asset_list_and_expenses') ? $assets : [];
        $data->bankBalance     = $request->has('bank_balance') ? $bankBalance : [];
        $data->utilityExpenses = [];
        $data->budgetVsActual  = $budgetVsActual;
        $data->generalFund     = $generalFund;
        $data->reservedFund    = $reservedFund;
        $data->collection      = $collection;

        $response = Http::withoutVerifying()->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => 'nCTX1Hde7PfWH14b3B1KVQ1R85QGMlaN',
        ])
            ->post('https://b2bgateway.dubailand.gov.ae/mollak/external/managementreport/submit', $data);

        // save datainto our database
        OaServiceRequest::create([
            'service_parameter_id' => 1,
            'property_group'       => $request->property_group,
            'property_name'        => $request->property_name,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'service_period'       => $request->service_period,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
            'oa_service_file'      => $folderPath,
        ]);

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

    public function getOaService(OaServiceRequest $oaService)
    {
        return new OaServiceRequestResource($oaService);
    }
}
