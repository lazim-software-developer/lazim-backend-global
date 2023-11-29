<?php

namespace App\Jobs\OAM;

use App\Models\Accounting\OAMReceipts;
use App\Models\Building\Building;
use App\Models\Building\Flat;
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

    public function __construct(Building $building)
    {
        $this->building = $building;
    }

    public function handle()
    {
        try {
            $propertyGroupId = $this->building->property_group_id;

            $response = Http::withoutVerifying()->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get(env("MOLLAK_API_URL") . '/sync/receipts/'.$propertyGroupId.'/1-Jan-2023/2-Apr-2023');

                $properties = $response->json()['response']['properties'];

            foreach ($properties as $property) {
                $flatId = Flat::where('mollak_property_id', $property['mollakPropertyId'])->value('id');
                foreach ($property['receipts'] as $receipt) {
                    OAMReceipts::create([
                        'receipt_number' => $receipt['receiptNumber'],
                        'receipt_date' => $receipt['receiptDate'],
                        'record_source' => $receipt['recordSource'],
                        'receipt_amount' => $receipt['receiptAmount'],
                        'receipt_created_date' => $receipt['receiptCreatedDate'],
                        'transaction_reference' => $receipt['transactionReference'],
                        'payment_mode' => $receipt['paymentMode'],
                        'virtual_account_description' => $receipt['virtualAccountDescription'],
                        'noqodi_info' => $receipt['noqodiInfo'] ? json_encode($receipt['noqodiInfo']) : null,
                        'payment_status' => $receipt['paymentStatus'],
                        'from_date' => '2023-01-01', // Adjust as needed
                        'to_date' => '2023-04-02',   // Adjust as needed
                        'building_id' => $this->building->id,
                        'flat_id' => $flatId,
                        'receipts' => '01-Jan-2023 To 31-Apr-2023'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch or save receipts: ' . $e->getMessage());
        }
    }
}
