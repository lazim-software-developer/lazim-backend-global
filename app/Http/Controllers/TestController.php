<?php

namespace App\Http\Controllers;

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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

        $data->propertyGroupId = $request->propertyGroupId;
        $data->fromDate        = $request->fromDate;
        $data->toDate          = $request->toDate;
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

}
