<?php

namespace App\Jobs\OAM;

use App\Models\Accounting\OAMReceipts;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchAndSaveReceipts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $building;

    public function __construct($building = null, protected $propertyGroupId = null, protected $mollakPropertyId = null, protected $receiptId = null)
    {
        $this->building = $building;
    }

    public function handle()
    {
        try {
            $propertyGroupId = $this->propertyGroupId ?: $this->building->property_group_id;
            $mollakPropertyId = $this->mollakPropertyId;
            $receiptId = $this->receiptId;
            $buildingId = $this->building?->id ?: Building::where('property_group_id', $propertyGroupId)->first()?->id;

            $now = new DateTime();

            // Get the start of the current week (Monday)
            $startOfWeek = (clone $now)->modify('monday this week')->format('d-M-Y');

            // Get the end of the current week (Sunday)
            $endOfWeek = (clone $now)->modify('sunday this week')->format('d-M-Y');

            if($this->receiptId){
                $url = 'https://qagate.dubailand.gov.ae/mollak/external/sync/receipts/' .$propertyGroupId."/".$mollakPropertyId."/".$receiptId."/id";

                Log::info('RECEIPTID', [$url]);
            }
            else{
                $url = env("MOLLAK_API_URL") . '/sync/receipts/' . $propertyGroupId . '/' . $startOfWeek . '/' . $endOfWeek;
                // $url = env("MOLLAK_API_URL") . '/sync/receipts/' . $propertyGroupId . '/' . $dateRange;
            }
            $response = Http::withoutVerifying()->retry(2, 500)->timeout(60)->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get($url);
            // ])->get(env("MOLLAK_API_URL") . '/sync/receipts/' . $propertyGroupId . '/' . $dateRange);

            Log::info('RESPONSE', [$response->json()]);

            $properties = $response->json()['response']['properties'];


            $currentQuarterDates = $this->getCurrentQuarterDates();

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
                    // $connection = DB::connection('lazim_accounts');
                    // $created_by = $connection->table('users')->where('owner_association_id', $flat->owner_association_id)->where('type', 'company')->first()?->id;
                    // // $invoiceId = $connection->table('invoices')->where('created_by', $created_by)->orderByDesc('invoice_id')->first()?->invoice_id + 1;
                    // $customerId = $connection->table('customer_flat')->where('flat_id', $flat->id)->where('building_id', $this->building->id)->where('active', true)->first()?->customer_id;
                    // $category_id = $connection->table('product_service_categories')->where('name', 'Service Charges')->first()?->id;
                    // $accountId = $connection->table('bank_accounts')->where('created_by', $created_by)->where('holder_name','Owner Account')->first()?->id;
                    // $connection->table('revenues')->insert([
                    //     'building_id' => $buildingId,
                    //     'flat_id' => $flat?->id,
                    //     'date' => $receipt['receiptDate'],
                    //     'amount' => $receipt['receiptAmount'],
                    //     'account_id' => $accountId,
                    //     'customer_id' => $customerId,
                    //     'category_id' => $category_id,
                    //     'payment_method' => 0,
                    //     'reference' => $receipt['transactionReference'],
                    //     'created_by' => $created_by,
                    //     'created_at' => now(),
                    //     'updated_at' => now(),
                    // ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch or save receipts: ' . $e->getMessage());
        }
    }

    // Helper function
    public static function getCurrentQuarterDateRange()
    {
        $currentDate = new DateTime();
        $currentMonth = (int)$currentDate->format('m');
        $currentYear = $currentDate->format('Y');

        // Determine the current quarter
        $currentQuarter = ceil($currentMonth / 3);

        // Define the start and end months for each quarter
        $quarterMonths = [
            1 => ['start' => '01-Jan', 'end' => '31-Mar'],
            2 => ['start' => '01-Apr', 'end' => '30-Jun'],
            3 => ['start' => '01-Jul', 'end' => '30-Sep'],
            4 => ['start' => '01-Oct', 'end' => '31-Dec'],
        ];

        // Get the start and end date for the current quarter
        $startDate = $quarterMonths[$currentQuarter]['start'] . '-' . $currentYear;
        $endDate = $quarterMonths[$currentQuarter]['end'] . '-' . $currentYear;

        return $startDate . '/' . $endDate;
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
}
