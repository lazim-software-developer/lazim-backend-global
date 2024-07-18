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

            $dateRange = $this->getCurrentQuarterDateRange();

            Log::info('RECEIPTID', [$receiptId]);

            if($this->receiptId){
                $url = 'https://qagate.dubailand.gov.ae/mollak/external/sync/receipts/' .$propertyGroupId."/".$mollakPropertyId."/".$receiptId."/id";    
            }
            else{
                // $url = env("MOLLAK_API_URL") . '/sync/receipts/' . $propertyGroupId . '/01-Jan-2024/31-Mar-2024';
                $url = env("MOLLAK_API_URL") . '/sync/receipts/' . $propertyGroupId . '/' . $dateRange;
            }
            $response = Http::withoutVerifying()->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get($url);
            // ])->get(env("MOLLAK_API_URL") . '/sync/receipts/' . $propertyGroupId . '/' . $dateRange);
            
            $properties = $response->json()['response']['properties'];

            Log::info($properties);
            $currentQuarterDates = $this->getCurrentQuarterDates();

            foreach ($properties as $property) {
                $flatId = Flat::where('mollak_property_id', $property['mollakPropertyId'])->value('id');
                foreach ($property['receipts'] as $receipt) {
                    OAMReceipts::updateOrCreate(
                        [
                            'receipt_number' => $receipt['receiptNumber'],
                            'receipt_date' => $receipt['receiptDate'],
                            'building_id' => $this->building->id,
                            'flat_id' => $flatId,
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
