<?php

namespace App\Jobs\OAM;

use App\Models\Accounting\OAMInvoice;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchAndSaveInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $building;

    public function __construct($building = null, protected $propertyGroupId = null, protected $quarterCode = null, protected $serviceChargeGroupId = null)
    {
        $this->building = $building;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $buildingId = $this->building?->id;
        $propertyGroupId = $this->propertyGroupId ?: $this->building->property_group_id;
        $serviceChargeGroupId = $this->serviceChargeGroupId;

        $currentDate = new DateTime();
        $currentYear = $currentDate->format('Y');
        $currentQuarter = ceil($currentDate->format('n') / 3);

        $quarter =$this->quarterCode ?: "Q" . $currentQuarter . "-JAN" . $currentYear . "-DEC" . $currentYear;

        // try {
            if(!$this->serviceChargeGroupId){
                $url = env("MOLLAK_API_URL") . '/sync/invoices/' . $propertyGroupId . '/all/' . $quarter;
                
            }
            else{
                $url = env("MOLLAK_API_URL") . "/sync/invoices/".$propertyGroupId."/".$serviceChargeGroupId."/".$quarter;
            }
                $response = Http::withoutVerifying()->withHeaders([
                    'content-type' => 'application/json',
                    'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
                ])->get($url);

                $invoicesData = $response->json()['response']['serviceChargeGroups'];

                Log::info('invoice'.$invoicesData);
                foreach ($invoicesData as $data) {
                    foreach ($data['properties'] as $property) {
                        $flat = Flat::where('mollak_property_id',  $property['mollakPropertyId'])->first();

                        // Save amount data
                        $generalFundAmount = 0;
                        $reservedFundAmount = 0;
                        $additionalCharges = 0;
                        $previousBalances = 0;
                        $adjustmentAmount = 0;

                        // Loop through invoice items to set the correct amounts
                        foreach ($property['invoiceItems'] as $item) {
                            switch ($item['itemName']['englishName']) {
                                case 'General Fund':
                                    $generalFundAmount = $item['amount'];
                                    break;
                                case 'Reserved Fund':
                                    $reservedFundAmount = $item['amount'];
                                    break;
                                case 'Additional Charges':
                                    $additionalCharges = $item['amount'];
                                    break;
                                case 'Previous Balances':
                                    $previousBalances = $item['amount'];
                                    break;
                                case 'Adjustment':
                                    $adjustmentAmount = $item['amount'];
                                    break;
                            }
                        }

                        OAMInvoice::updateOrCreate(
                            [
                                'building_id' => $buildingId,
                                'flat_id' => $flat->id,
                                'invoice_number' => $property['invoiceNumber'],
                                'invoice_quarter' => $data['invoiceQuarter'],
                                'invoice_period' => $data['invoicePeriod'],
                                'budget_period' => $data['budgetPeriod'],
                                'service_charge_group_id' => $data['serviceChargeGroupId'],
                            ],
                            [
                                'invoice_date' => $property['invoiceDate'],
                                'invoice_status' => $property['invoiceStatus']['englishName'],
                                'due_amount' => $property['dueAmount'],
                                'general_fund_amount' => $generalFundAmount,
                                'reserve_fund_amount' => $reservedFundAmount,
                                'additional_charges' => $additionalCharges,
                                'previous_balance' => $previousBalances,
                                'adjust_amount' => $adjustmentAmount,
                                'invoice_due_date' => $property['invoiceDueDate'],
                                'invoice_pdf_link' => $property['invoiceDetailUrl'] ?? null,
                                'invoice_detail_link' => $property['invoicePDF'] ?? null,
                                'invoice_amount' => $property['invoiceAmount'],
                                'amount_paid' => 0,
                                'updated_by' => User::first()->id,
                                'type' => 'service_charge',
                                'payment_url' => $property['paymentUrl']
                            ]
                        );
                    }
                }
        // } catch (\Exception $e) {
        //     Log::error('Failed to fetch or save invoices');
        // }
    }
}
