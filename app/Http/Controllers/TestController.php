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
            'consumer-id'  => 'dqHdShhrZQgeSY9a4BZh6cgucpQJvS5r',
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
        return OaServiceRequestResource::collection(OaServiceRequest::paginate(10));
    }

    public function getOaService(OaServiceRequest $oaService)
    {
        return new OaServiceRequestResource($oaService);
    }
}
