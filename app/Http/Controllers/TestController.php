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
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function uploadAll(Request $request)
    {
        $folderPath = now()->timestamp;
        $mimeType = "xlsx";

        $files = [
            'e_services' => ServiceImport::class,
            'happiness_center' => HappinessCenterImport::class,
            'balance_sheet' => BalanceSheetImport::class,
            'accounts_payables' => AccountsPayablesImport::class,
            'delinquents' => DelinquentsImport::class,
            'work_orders' => WorkOrdersImport::class,
            'reserve_fund' => ReserveFundImport::class,
            'budget_vs_actual' => BudgetVsActualImport::class,
            'general_fund_statement' => CentralFundStatementImport::class,
            'collections' => CollectionImport::class,
            'bank_balance' => BankBalanceImport::class,
            'asset_list_and_expenses' => AssetsImport::class,
            'utility_expenses' => UtilityExpensesImport::class,
        ];

        $data = new stdClass();
        foreach ($files as $key => $importClass) {
            if ($request->has($key)) {
                $importInstance = new $importClass;
                Excel::import($importInstance, $request->file($key));
                $data->{ucfirst(Str::camel($key))} = $importInstance->data ?? $importInstance->getResults();

                Storage::disk('s3')->put($folderPath . '/' . $key . '.' . $mimeType,
                    file_get_contents($request->file($key)));
            } else {
                $data->{ucfirst(Str::camel($key))} = $this->getDefaultData($key);
            }
        }

        $data->PropertyGroupId = $request->property_group;
        $data->FromDate = $request->from_date;
        $data->ToDate = $request->to_date;

        Log::info(json_encode((array) $data));

        $response = Http::withOptions(['verify' => false])
            ->retry(3, 100)
            ->timeout(60)
            ->withHeaders([
                'content-type' => 'application/json',
                'consumer-id' => env("MOLLAK_CONSUMER_ID"),
            ])->post(env("MOLLAK_API_URL") . '/managementreport/submit', $data);

        Log::info($response);

        $response = json_decode($response->body());
        $oaData = OaServiceRequest::create([
            'service_parameter_id' => 1,
            'property_group' => $request->property_group,
            'property_name' => Building::where('property_group_id', $request->property_group)->first()->name,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'service_period' => $request->service_period,
            'status' => 'Posted',
            'uploaded_by' => 1,
            'oa_service_file' => $folderPath,
        ]);

        if ($response->responseCode === 200) {
            $oaData->update(['status' => "Success", 'mollak_id' => $response->response->id]);
            Notification::make()->title("Upload successfully")->success()->send();
        } else {
            Log::info(json_encode($response));
            $oaData->update(['status' => "Failed"]);
            $this->handleErrors($response);
        }
    }

    private function getDefaultData($key)
    {
        $defaults = [
            'balance_sheet' => (object) [
                'income' => [],
                'expense' => [],
                'asset' => [],
                'liability' => [],
                'equity' => [],
            ],
            'reserve_fund' => (object) [
                'income' => [],
                'expense' => [],
            ],
            'budget_vs_actual' => (object) [
                'expense_accounts' => [],
                'income_accounts' => [],
            ],
            'general_fund_statement' => (object) [
                'income' => [],
                'expense' => [],
            ],
            'collection' => (object) [
                'by_method' => [],
                'recovery' => new stdClass,
            ],
            'bank_balance' => (object) [
                'statement' => new stdClass,
                'bankbook' => new stdClass,
            ],
        ];

        return $defaults[$key] ?? [];
    }

    private function handleErrors($response)
    {
        $errorMessages = '';

        if (isset($response->validationErrorsList)) {
            $errors = array_map(function ($validationError) {
                $errorItems = $validationError->items ?? [];

                if (empty($errorItems) && isset($validationError->errorMessage)) {
                    // Handle general error message when items are not available
                    $errorItems[] = "Failed to upload files: " . $validationError->errorMessage;
                }

                return array_map(function ($item) use ($validationError) {
                    $filename = $item->key ?? 'Unknown file';
                    $errorMessage = $validationError->errorMessage ?? 'Unknown error';
                    return "Failed to upload file: $filename. Issue: $errorMessage";
                }, $errorItems);
            }, $response->validationErrorsList);

            // Flatten the array of error messages
            $errors = array_merge(...$errors);

            // Combine errors into a single string
            $errorMessages = implode("\n", $errors);
        }

        // Send notification with error details
        Notification::make()
            ->title("Upload failed")
            ->danger()
            ->body($errorMessages ?: "There seems to be some issue with the files you are uploading. Please check and try again!")
            ->send();
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

    public function forwardRequest(Request $request)
    {
        $url = $request->input('url'); // Get the URL from the request
        $body = $request->input('body'); // Get the body from the request
        $method = strtoupper($request->input('method', 'POST'));

        try {
            $httpRequest  = Http::withOptions(['verify' => false])
                            ->withHeaders([
                                'Content-Type' => 'application/json',
                                'consumer-id' => env('MOLLAK_CONSUMER_ID'),
                            ]);

            switch ($method) {
                case 'GET':
                    $response = $httpRequest->get(env('MOLLAK_API_URL') . $url);
                    break;
                case 'POST':
                    $response = $httpRequest->post(env('MOLLAK_API_URL') . $url, $body);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported HTTP method: {$method}");
            }
            return response()->json([
                'status' => 'success',
                'statusCode' => $response->status(),
                'data' => $response->json(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
