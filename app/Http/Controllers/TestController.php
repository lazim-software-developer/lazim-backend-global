<?php

namespace App\Http\Controllers;

use App\Http\Resources\OaServiceRequestResource;
use App\Http\Resources\ServiceParameterResource;
use App\Imports\AccountsPayablesImport;
use App\Imports\AssetsImport;
use App\Imports\BalanceSheetImport;
use App\Imports\TestImport;
use App\Imports\ServiceImport;
use App\Imports\CollectionImport;
use App\Imports\UtilityExpensesImport;
use App\Imports\BankBalanceImport;
use App\Imports\BudgetVsActualImport;
use App\Imports\CentralFundStatementImport;
use App\Imports\DelinquentsImport;
use App\Imports\HappinessCenterImport;
use App\Imports\ReserveFundImport;
use App\Imports\WorkOrdersImport;
use App\Models\OaServiceRequest;
use App\Models\ServiceParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use \stdClass;

class TestController extends Controller
{
    public function uploadAll(Request $request)
    {
        $parameters = ServiceParameter::all();

        $folderPath = now()->timestamp;

        $mimeType = "xlsx";
        
        // E services
        if ($request->has('e_services')) {
            $serviceImport = new ServiceImport;

            Excel::import($serviceImport, $request->file('e_services'));
            $e_services = $serviceImport->data;

            $document = $request->e_services;
            $fileName = 'e_services';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $e_services = [];
        }

        // Happiness center
        if ($request->has('happiness_center')) {
            $happinesscenterimport = new HappinessCenterImport;

            Excel::import($happinesscenterimport, $request->file('happiness_center'));
            $happiness_center = $happinesscenterimport->data;

            $document = $request->happiness_center;
            $fileName = 'happiness_center';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $happiness_center = [];
        }

        if ($request->has('balance_sheet')) {

            $BalanceSheetImport = new BalanceSheetImport;

            Excel::import($BalanceSheetImport, $request->file('balance_sheet'));

            $balance_sheet = $BalanceSheetImport->data;

            $document = $request->balance_sheet;
            
            $fileName = 'balance_sheet';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $balance_sheet = new stdClass;

            $balance_sheet->income = [];
            $balance_sheet->expense  = [];
            $balance_sheet->asset  = [];
            $balance_sheet->liability  = [];
            $balance_sheet->equity  = [];
        }
        
        // Account payables 
        if ($request->has('accounts_payables')) {
            $accountspayablesimport = new AccountsPayablesImport ;

            Excel::import($accountspayablesimport, $request->file('accounts_payables'));
            $accounts_payables = $accountspayablesimport->data;

            $document = $request->accounts_payables;
            
            $fileName = 'accounts_payables';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $accounts_payables = [];
        }

        // Delinquents
        if ($request->has('delinquents')) {
            $delinquentsImport = new DelinquentsImport;

            Excel::import($delinquentsImport, $request->file('delinquents'));
            $delinquents = $delinquentsImport->data;
            
            $document = $request->delinquents;
            
            $fileName = 'delinquents';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $delinquents = [];
        }

        // Work orders
        if ($request->has('work_orders')) {
            $workordersimport = new WorkOrdersImport;

            Excel::import($workordersimport, $request->file('work_orders'));
            $work_orders = $workordersimport->data;

            $document = $request->work_orders;
            
            $fileName = 'work_orders';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $work_orders = [];
        }

        if ($request->has('reserve_fund')) {

            $ReserveFundImport = new ReserveFundImport;

            Excel::import($ReserveFundImport, $request->file('reserve_fund'));
            $reserve_fund = $ReserveFundImport->data;

            $document = $request->reserve_fund;
            
            $fileName = 'reserve_fund';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $reserve_fund = new stdClass;

            $reserve_fund->income = [];
            $reserve_fund->expense  = [];
        }

        if ($request->has('budget_vs_actual')) {

            $budgetvsactual = new BudgetVsActualImport;

            Excel::import($budgetvsactual, $request->file('budget_vs_actual'));
            $budget_vs_actual = $budgetvsactual->data;

            $document = $request->budget_vs_actual;
            
            $fileName = 'budget_vs_actual';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $budget_vs_actual = new stdClass;
            $budget_vs_actual->expense_accounts = [];
            $budget_vs_actual->income_accounts = [];
        }

        if ($request->has('general_fund_statement')) {

            $CentralFundStatementImport = new CentralFundStatementImport;

            Excel::import($CentralFundStatementImport, $request->file('general_fund_statement'));
            $general_fund_statement = $CentralFundStatementImport->data;

            $document = $request->general_fund_statement;
            
            $fileName = 'general_fund_statement';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $general_fund_statement = new stdClass;

            $general_fund_statement->income = [];
            $general_fund_statement->expense = [];
        }

        if ($request->has('collections')) {

            $collectionImport = new CollectionImport;

            Excel::import($collectionImport, $request->file('collections'));

            $collection = $collectionImport->data;

            $document = $request->collections;
            
            $fileName = 'collections';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $collection = new stdClass;

            $collection->by_method = [];
            $collection->recovery  = new stdClass;
        }

        if ($request->has('bank_balance')) {
            $bankBalanceimport = new BankBalanceImport;
            
            Excel::import($bankBalanceimport, $request->file('bank_balance'));

            $bankBalance = $bankBalanceimport->data;

            $document = $request->bank_balance;
            
            $fileName = 'bank_balance';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $bankBalance = new stdClass;
            $bankBalance->statement = new stdClass;
            $bankBalance->bankbook =  new stdClass;
        }

        if ($request->has('asset_list_and_expenses')) {
            $import = new AssetsImport;

            Excel::import($import, $request->file('asset_list_and_expenses'));
            $assets = $structuredData = $import->getResults();

            $document = $request->asset_list_and_expenses;
            
            $fileName = 'asset_list_and_expenses';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $assets = [];
        }

        if ($request->has('utility_expenses')) {
            $uaImport = new UtilityExpensesImport;

            Excel::import($uaImport, $request->file('utility_expenses'));
            $utility = $uaImport->getResults();

            $document = $request->utility_expenses;
            
            $fileName = 'utility_expenses';

            Storage::disk('s3')->put($folderPath . '/' . $fileName . '.' . $mimeType,
                file_get_contents($document));
        } else {
            $utility = [];
        }

        $data = new stdClass();

        $data->propertyGroupId = $request->property_group;
        $data->fromDate        = $request->from_date;
        $data->toDate          = $request->to_date;
        $data->delinquents     = $delinquents;
        $data->eservices       = $e_services;
        $data->happinessCenter = $happiness_center;
        $data->balanceSheet    = $balance_sheet;
        $data->accountsPayable = $accounts_payables;
        $data->workOrders      = $work_orders;
        $data->assets          = $assets;
        $data->bankBalance     = $bankBalance;
        $data->utilityExpenses = $utility;
        $data->budgetVsActual  = $budget_vs_actual;
        $data->generalFund     = $general_fund_statement;
        $data->reservedFund    = $reserve_fund;
        $data->collection      = $collection;

        $response = Http::withoutVerifying()->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->post(env("MOLLAK_API_URL").'/managementreport/submit', $data);

        $response = json_decode($response->body());

        $oaData = OaServiceRequest::create([
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

        //return $response;

        if($response->responseCode === 200) {
            $oaData->update(['status' => "Success", 'mollak_id' => $response->response->id]);
            return response()->json(['status' => 'success', 'message' => "Uploaded successfully!"]);
        } else {
            $oaData->update(['status' => "Failed"]);
            return response()->json(['status' => 'error', 'message' => "There seems to be some issue with the files you are uploading. Please check and try again!"]);
        }

    }
    public function serviceParameters()
    {
        return ServiceParameterResource::collection(ServiceParameter::all());
    }
    public function serviceRequest()
    {
        return OaServiceRequestResource::collection(OaServiceRequest::paginate(10));
    }

    public function getOaService(OaServiceRequest $oaService)
    {
        return new OaServiceRequestResource($oaService);
    }
}
