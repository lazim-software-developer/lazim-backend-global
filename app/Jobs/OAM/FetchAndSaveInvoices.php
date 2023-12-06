<?php

namespace App\Jobs\OAM;

use App\Models\Accounting\OAMInvoice;
use App\Models\Building\Building;
use App\Models\Building\Flat;
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

    public function __construct(Building $building)
    {
        $this->building = $building;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $buildingId = $this->building->id;
        $propertyGroupId = $this->building->property_group_id;

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get(env("MOLLAK_API_URL") . '/sync/invoices/'. $propertyGroupId .'/all/Q4-JAN2023-DEC2023');

                $invoicesData = $response->json()['response']['serviceChargeGroups'];
                
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

                    OAMInvoice::create([
                        'building_id' => $buildingId,
                        'flat_id' => $flat->id,'invoice_number' => $property['invoiceNumber'],
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
                        'updated_by' => 1,
                        'type' => 'service_charge',
                        'invoice_quarter' => $data['invoiceQuarter'],
                        'invoice_period' => $data['invoicePeriod'],
                        'budget_period' => $data['budgetPeriod'],
                        'service_charge_group_id' => $data['serviceChargeGroupId'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch or save invoices: ' . $e->getMessage());
        }
    }
}
