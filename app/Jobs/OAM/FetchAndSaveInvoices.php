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
            ])->get(env("MOLLAK_API_URL") . '/sync/invoices/'. $propertyGroupId .'/all/Q4-JAN2019-DEC2019');

                $invoicesData = $response->json()['response']['serviceChargeGroups'];
                
                foreach ($invoicesData as $data) {
                    foreach ($data['properties'] as $property) {
                        $flat = Flat::where('mollak_property_id',  $property['mollakPropertyId'])->first();
                        
                    OAMInvoice::create([
                        'building_id' => $buildingId,
                        'flat_id' => $flat->id,
                        'invoice_number' => $property['invoiceNumber'],
                        'invoice_date' => $property['invoiceDate'],
                        'invoice_status' => $property['invoiceStatus']['englishName'],
                        'due_amount' => $property['dueAmount'],
                        'general_fund_amount' => $property['invoiceItems'][0]['amount'],
                        'reserve_fund_amount' => $property['invoiceItems'][1]['amount'],
                        'additional_charges' => $property['invoiceItems'][1]['amount'],
                        'previous_balance' => $property['invoiceItems'][1]['amount'],
                        'adjust_amount' => $property['invoiceItems'][1]['amount'],
                        'invoice_due_date' => $property['invoiceDueDate'],
                        'invoice_pdf_link' => $property['invoiceDetailUrl'] ?? null,
                        'invoice_detail_link' => $property['invoicePDF'] ?? null,
                        'updated_by' => 1,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch or save invoices: ' . $e->getMessage());
        }
    }
}
