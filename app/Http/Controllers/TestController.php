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
use App\Jobs\MailTestJob;
use App\Models\AccountCredentials;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\OaServiceRequest;
use App\Models\ServiceParameter;
use App\Models\User\User;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Carbon\Carbon;
use DateTime;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                $propertyName = $this->mapKeyToProperty($key);
                $data->$propertyName = $importInstance->data ?? $importInstance->getResults();

                Storage::disk('s3')->put($folderPath . '/' . $key . '.' . $mimeType,
                    file_get_contents($request->file($key)));
            } else {
                $propertyName = $this->mapKeyToProperty($key);
                $data->$propertyName = $this->getDefaultData($key) ?? [];
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
                    return $filename ? "Failed to upload file: $filename. Issue: $errorMessage" : "Issue: $errorMessage";
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

        throw new \Exception();
    }

    private function mapKeyToProperty($key)
    {
        $mapping = [
            'accounts_payables' => 'AccountsPayable',
            'general_fund_statement' => 'GeneralFund',
            'reserve_fund' => 'ReservedFund',
            'collections' => 'Collection',
            'asset_list_and_expenses' => 'Assets',
            'delinquents' => 'Delinquents',
            'e_services' => 'Eservices',
            'happiness_center' => 'HappinessCenter',
            'balance_sheet' => 'BalanceSheet',
            'work_orders' => 'WorkOrders',
            'bank_balance' => 'BankBalance',
            'utility_expenses' => 'UtilityExpenses',
            'budget_vs_actual' => 'BudgetVsActual'
        ];

        return $mapping[$key] ?? ucfirst(Str::camel($key));
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

    public function emailTriggering(Request $request){

        $credentials = AccountCredentials::where('oa_id', $request->oa_id)->first();

        MailTestJob::dispatch($credentials);

    }

    public function invoiceTest(Request $request)
    // {
    //     // return $request->serviceChargeGroups;
    //     $invoicesData =$request->serviceChargeGroups;
    //     $buildingId = Building::where('property_group_id', 235553)->first()?->id;
    //     // return Flat::where('mollak_property_id',  17651620)->first();
    //     foreach ($invoicesData as $data) {
    //         foreach ($data['properties'] as $property) {
    //             $flat = Flat::where('mollak_property_id',  $property['mollakPropertyId'])->first();

    //             // Save amount data
    //             $generalFundAmount = 0;
    //             $reservedFundAmount = 0;
    //             $additionalCharges = 0;
    //             $previousBalances = 0;
    //             $adjustmentAmount = 0;

    //             // Loop through invoice items to set the correct amounts
    //             foreach ($property['invoiceItems'] as $item) {
    //                 switch ($item['itemName']['englishName']) {
    //                     case 'General Fund':
    //                         $generalFundAmount = $item['amount'];
    //                         break;
    //                     case 'Reserved Fund':
    //                         $reservedFundAmount = $item['amount'];
    //                         break;
    //                     case 'Additional Charges':
    //                         $additionalCharges = $item['amount'];
    //                         break;
    //                     case 'Previous Balances':
    //                         $previousBalances = $item['amount'];
    //                         break;
    //                     case 'Adjustment':
    //                         $adjustmentAmount = $item['amount'];
    //                         break;
    //                 }
    //             }

    //             OAMInvoice::updateOrCreate(
    //                 [
    //                     'building_id' => $buildingId,
    //                     'flat_id' => $flat->id,
    //                     'invoice_number' => $property['invoiceNumber'],
    //                     'invoice_quarter' => $data['invoiceQuarter'],
    //                     'invoice_period' => $data['invoicePeriod'],
    //                     'budget_period' => $data['budgetPeriod'],
    //                     'service_charge_group_id' => $data['serviceChargeGroupId'],
    //                 ],
    //                 [
    //                     'invoice_date' => $property['invoiceDate'],
    //                     'invoice_status' => $property['invoiceStatus']['englishName'],
    //                     'due_amount' => $property['dueAmount'],
    //                     'general_fund_amount' => $generalFundAmount,
    //                     'reserve_fund_amount' => $reservedFundAmount,
    //                     'additional_charges' => $additionalCharges,
    //                     'previous_balance' => $previousBalances,
    //                     'adjust_amount' => $adjustmentAmount,
    //                     'invoice_due_date' => $property['invoiceDueDate'],
    //                     'invoice_pdf_link' => $property['invoiceDetailUrl'] ?? null,
    //                     'invoice_detail_link' => $property['invoicePDF'] ?? null,
    //                     'invoice_amount' => $property['invoiceAmount'],
    //                     'amount_paid' => 0,
    //                     'updated_by' => User::first()->id,
    //                     'type' => 'service_charge',
    //                     'payment_url' => $property['paymentUrl'],
    //                     'owner_association_id' => $flat->owner_association_id
    //                 ]
    //             );
    //             $connection = DB::connection('lazim_accounts');
    //             $created_by = $connection->table('users')->where('owner_association_id', $flat->owner_association_id)->where('type', 'company')->first()?->id;
    //             $invoiceId = $connection->table('invoices')->where('created_by', $created_by)->orderByDesc('invoice_id')->first()?->invoice_id + 1;
    //             $customerId = $connection->table('customer_flat')->where('flat_id',$flat->id)->where('building_id' , $buildingId)->where('active',true)->first()?->customer_id;
    //             $category_id = $connection->table('product_service_categories')->where('name','Service Charges')->first()?->id;
    //             $connection->table('invoices')->insert([
    //                 'invoice_id' => $invoiceId,
    //                 'customer_id' => $customerId,
    //                 'issue_date' => $property['invoiceDate'],
    //                 'due_date' => $property['invoiceDueDate'],
    //                 'send_date' =>$property['invoiceDate'],
    //                 'category_id' => $category_id,
    //                 'ref_number' => random_int(11111111,99999999),
    //                 'status' => false,
    //                 'shipping_display' => true,
    //                 'discount_apply' => false,
    //                 'created_by' => $created_by,
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ]);
    //         }
    //     }
    // }
    {
        // return $request->properties;
        $properties = $request->properties;
        $currentQuarterDates = $this->getCurrentQuarterDates();
        $buildingId = Building::where('property_group_id', 235553)->first()?->id;

            foreach ($properties as $property) {
                $flat = Flat::where('mollak_property_id', $property['mollakPropertyId'])->first();
                foreach ($property['receipts'] as $receipt) {
                    OAMReceipts::updateOrCreate(
                        [
                            'receipt_number' => $receipt['receiptNumber'],
                            'receipt_date' => $receipt['receiptDate'],
                            'building_id' => $buildingId,
                            'flat_id' => $flat?->id,
                        ],
                        [
                            'transaction_reference' => $receipt['transactionReference'],
                            'record_source' => $receipt['recordSource'],
                            'receipt_amount' => $receipt['receiptAmount'],
                            'receipt_created_date' => $receipt['receiptCreatedDate'],
                            'payment_mode' => $receipt['paymentMode'],
                            'virtual_account_description' => $receipt['virtualAccountDescription'],
                            'noqodi_info' => $receipt['noqodiInfo'] ? json_encode($receipt['noqodiInfo']) : null,
                            'payment_status' => $receipt['paymentStatus'],
                            'from_date' => $currentQuarterDates['from_date'],
                            'to_date' => $currentQuarterDates['to_date'],
                            'receipt_period' => $currentQuarterDates['receipt_period']
                        ]
                    );
                    $connection = DB::connection('lazim_accounts');
                    $created_by = $connection->table('users')->where('owner_association_id', $flat->owner_association_id)->where('type', 'company')->first()?->id;
                    // $invoiceId = $connection->table('invoices')->where('created_by', $created_by)->orderByDesc('invoice_id')->first()?->invoice_id + 1;
                    $customerId = $connection->table('customer_flat')->where('flat_id', $flat->id)->where('building_id', $buildingId)->where('active', true)->first()?->customer_id;
                    $category_id = $connection->table('product_service_categories')->where('name', 'Service Charges')->first()?->id;
                    $accountId = $connection->table('bank_accounts')->where('created_by', $created_by)->where('holder_name','Owner Account')->first()?->id;
                    $connection->table('revenues')->insert([
                        'date' => $receipt['receiptDate'],
                        'amount' => $receipt['receiptAmount'],
                        'account_id' => $accountId,
                        'customer_id' => $customerId,
                        'category_id' => $category_id,
                        'payment_method' => 0,
                        'reference' => random_int(11111111, 99999999),
                        'created_by' => $created_by,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
    }

    public static function getCurrentQuarterDates()
    {
        $currentDate = new DateTime();
        $currentYear = $currentDate->format('Y');
        $currentQuarter = ceil($currentDate->format('n') / 3);

        // Define start and end months for each quarter
        $quarterMonths = [
            1 => ['start' => '01-Jan', 'end' => '31-Mar'],
            2 => ['start' => '01-Apr', 'end' => '30-Jun'],
            3 => ['start' => '01-Jul', 'end' => '30-Sep'],
            4 => ['start' => '01-Oct', 'end' => '31-Dec'],
        ];

        $startMonthDay = $quarterMonths[$currentQuarter]['start'];
        $endMonthDay = $quarterMonths[$currentQuarter]['end'];

        // Format dates
        $fromDate = DateTime::createFromFormat('d-M-Y', $startMonthDay . '-' . $currentYear)->format('Y-m-d');
        $toDate = DateTime::createFromFormat('d-M-Y', $endMonthDay . '-' . $currentYear)->format('Y-m-d');
        $receiptPeriod = str_replace('-', ' ', $startMonthDay) . ' To ' . str_replace('-', ' ', $endMonthDay) . '-' . $currentYear;

        return [
            'from_date' => '2024-01-01',
            'to_date' => '2024-03-31',
            'receipt_period' => '01-Jan-2024 To 31-Mar-2024'
        ];
    }

    public function redirectBasedOnOS(Request $request)
    {
        // Get the User-Agent string
        $userAgent = $request->header('User-Agent');

        // Check the OS
        if (preg_match('/Android/i', $userAgent)) {
            // Redirect to Android-specific URL
            return redirect('https://play.google.com/store/apps/details?id=com.punithgoud.lazim&hl=en_IN&gl=US');
        } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
            // Redirect to iOS-specific URL
            return redirect('https://apps.apple.com/nz/app/lazim/id6475393837');
        } else {
            // Default redirection if OS is not recognized
            return redirect('https://resident.lazim.ae/');
        }
    }
}
