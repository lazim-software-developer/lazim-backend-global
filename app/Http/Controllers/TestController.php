<?php

namespace App\Http\Controllers;

use App\Http\Resources\OaServiceRequestResource;
use App\Http\Resources\ServiceParameterResource;
use App\Imports\AccountsPayablesImport;
use App\Imports\AssetsImport;
use App\Imports\BalanceSheetImport;
use App\Imports\BankBalanceImport;
use App\Imports\BudgetVsActualImport;
use App\Imports\CentralFundStatementImport;
use App\Imports\CollectionImport;
use App\Imports\DelinquentsImport;
use App\Imports\HappinessCenterImport;
use App\Imports\ReserveFundImport;
use App\Imports\ServiceImport;
use App\Imports\UtilityExpensesImport;
use App\Imports\WorkOrdersImport;
use App\Models\Building\Building;
use App\Models\OaServiceRequest;
use App\Models\ServiceParameter;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Snowfire\Beautymail\Beautymail;
use \stdClass;
use ZipArchive;

class TestController extends Controller
{
    public function uploadAll(Request $request)
    {
        // dd($request);
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

            $balance_sheet->income    = [];
            $balance_sheet->expense   = [];
            $balance_sheet->asset     = [];
            $balance_sheet->liability = [];
            $balance_sheet->equity    = [];
        }

        // Account payables
        if ($request->has('accounts_payables')) {
            $accountspayablesimport = new AccountsPayablesImport;

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

            $reserve_fund->income  = [];
            $reserve_fund->expense = [];
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
            $budget_vs_actual                   = new stdClass;
            $budget_vs_actual->expense_accounts = [];
            $budget_vs_actual->income_accounts  = [];
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

            $general_fund_statement->income  = [];
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
            $bankBalance            = new stdClass;
            $bankBalance->statement = new stdClass;
            $bankBalance->bankbook  = new stdClass;
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

        $data->PropertyGroupId = $request->property_group;
        $data->FromDate        = $request->from_date;
        $data->ToDate          = $request->to_date;
        $data->Delinquents     = $delinquents;
        $data->Eservices       = $e_services;
        $data->HappinessCenter = $happiness_center;
        $data->BalanceSheet    = $balance_sheet;
        $data->AccountsPayable = $accounts_payables;
        $data->WorkOrders      = $work_orders;
        $data->Assets          = $assets;
        $data->BankBalance     = $bankBalance;
        $data->UtilityExpenses = $utility;
        $data->BudgetVsActual  = $budget_vs_actual;
        $data->GeneralFund     = $general_fund_statement;
        $data->ReservedFund    = $reserve_fund;
        $data->Collection      = $collection;

        // return $data;
        $response = Http::withOptions(['verify' => false])->retry(3, 100)->timeout(60)->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->post('https://qagate.dubailand.gov.ae/mollak/external/managementreport/submit', $data);
//env("MOLLAK_API_URL") . 
        Log::info($response);
        // return json_decode($response);
        $response = json_decode($response->body());

        $oaData = OaServiceRequest::create([
            'service_parameter_id' => 1,
            'property_group'       => $request->property_group,
            'property_name'        => Building::where('property_group_id',$request->property_group)->first()->name,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'service_period'       => $request->service_period,
            'status'               => 'Posted',
            'uploaded_by'          => 1,
            'oa_service_file'      => $folderPath,
        ]);

        // return $response;
        
        if ($response->responseCode === 200) {
            $oaData->update(['status' => "Success", 'mollak_id' => $response->response->id]);
            Notification::make()
                ->title("Upload successfully")
                ->success()
                ->send();
            // return response()->json(['status' => 'success', 'message' => "Uploaded successfully!"]);
        } else {
            Log::info(json_encode($response));
            $oaData->update(['status' => "Failed"]);
            $errorMessages = '';
            if (isset($response->validationErrorsList)) {
                $errors = array_map(function($validationError) {
                    // Check if $validationError->items is an array
                    if (is_array($validationError->items)) {
                        return array_map(function($item) use ($validationError) {
                            return "Failed to upload file: " . $item->key . ", There was an issue with the: " . $validationError->errorMessage;
                        }, $validationError->items);
                    } else if (isset($validationError->errorMessage)) {
                        $parts = explode(': ', $validationError->errorMessage);
                        $filename = isset($parts[1]) ? $parts[1] : 'Unknown';
                        return ["Failed to upload file: There was an issue with the " . $filename];
                    } else {
                        return [];
                    }
                }, $response->validationErrorsList);
                
                // Flatten the array
                $errors = array_merge(...$errors);
                
                // Join errors into a single string separated by newlines
                $errorMessages = implode("\n", $errors);
            }
            
            Notification::make()
                ->title("Upload failed")
                ->danger()
                ->body(function () use ($errorMessages) {
                    if (!empty($errorMessages)) {
                        return $errorMessages;
                    } else {
                        return "There seems to be some issue with the files you are uploading. Please check and try again!";
                    }
                })
                ->send();
            // return response()->json(['status' => 'error', 'message' => "There seems to be some issue with the files you are uploading. Please check and try again!"]);
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

    // public function sendMail(){

    //     $beautymail = app()->make(Beautymail::class);
    //     $beautymail->send('emails.testmail', function ($message) {
    //         $message
    //             ->to('prashanth@zysk.tech', 'Prashanth')
    //             ->subject('Welcome to Lazim! 🎉 Download Our App Now!');
    //     });
    // } 

    public function download(Request $request)
    {
        $templateMap = [
            'e_services' => 'templates/Services-Requests.xlsx',
            'happiness_center' => 'templates/Happiness-Center.xlsx',
            'balance_sheet' => 'templates/Balance-Sheet.xlsx',
            'general_fund_statement'=> 'templates/General-Fund-Statement.xlsx',
            'reserve_fund'=> 'templates/Reserve-Fund-Statement.xlsx',
            'budget_vs_actual'=> 'templates/Budget-Vs-Actual.xlsx',
            'accounts_payables'=> 'templates/Accounts-Payables.xlsx',
            'delinquents'=> 'templates/Delinquent-Owners.xlsx',
            'collections'=> 'templates/Collection-Report.xlsx',
            'bank_balance'=> 'templates/Bank-Balance.xlsx',
            'utility_expenses'=> 'templates/Utility-Expenses.xlsx',
            'work_orders'=> 'templates/Work-Orders.xlsx',
            'asset_list_and_expenses'=> 'templates/Asset-Lists-and-Expenses.xlsx',
        ];
        $template = $request->input('template');

        $s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => 'ap-south-1',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'http'        => [
                'verify' => false
            ]
        ]);
        
        // The file you want to retrieve the mime type for
        $bucket = 'lazim-dev';
        if ($template == 'all') {
            $zip = new ZipArchive();
            $zipFileName = 'all_templates.zip';
            if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
                foreach ($templateMap as $name => $key) {
                    try {
                        $result = $s3Client->getObject([
                            'Bucket' => $bucket,
                            'Key'    => $key
                        ]);
                        $fileContent = $result['Body']->getContents();
                        $zip->addFromString(basename($key), $fileContent);
                    } catch (AwsException $e) {
                        return response()->json(['error' => $e->getMessage()], 500);
                    }
                }
                $zip->close();
    
                return response()->download($zipFileName)->deleteFileAfterSend(true);
            }
        } else {
            $key = $templateMap[$template];
            try {
                $result = $s3Client->getObject([
                    'Bucket' => $bucket,
                    'Key'    => $key
                ]);
                $content = $result['Body']->getContents();
                $mimeType = $result['ContentType'];
                return Response::make($content, 200, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'attachment; filename="' . $template . '.xlsx' . '"',
                ]);
            } catch (AwsException $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }
    
}
