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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchAndSaveInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $building;

    public function __construct($building = null, protected $propertyGroupId = null, protected $serviceChargeGroupId = null, protected $quarterCode = null)
    {
        $this->building = $building;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $propertyGroupId = $this->propertyGroupId ?: $this->building->property_group_id;
        $serviceChargeGroupId = $this->serviceChargeGroupId;
        $buildingId = $this->building?->id ?: Building::where('property_group_id', $propertyGroupId)->first()?->id;

        $currentDate = new DateTime();
        $currentYear = $currentDate->format('Y');
        $currentQuarter = ceil($currentDate->format('n') / 3);

        $quarter = $this->quarterCode ?: "Q" . $currentQuarter . "-JAN" . $currentYear . "-DEC" . $currentYear;

        try {
            if (!$this->serviceChargeGroupId) {
                // $url = env("MOLLAK_API_URL") . '/sync/invoices/' . $propertyGroupId . '/all/' . $quarter;
                $url = env("MOLLAK_API_URL") ."/sync/invoices/". $propertyGroupId ."/all/Q1-JAN2023-DEC2023";
            } else {
                $url = env("MOLLAK_API_URL") . "/sync/invoices/" . $propertyGroupId . "/" . $serviceChargeGroupId . "/" . $quarter;
            }
            $response = Http::withoutVerifying()->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get($url);

            Log::info('RESPONSE', [$response->json()]);

            $invoicesData = $response->json()['response']['serviceChargeGroups'];

            // Log::info('invoice'.json_encode($invoicesData));
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
                            'payment_url' => $property['paymentUrl'],
                            'owner_association_id' => $flat->owner_association_id
                        ]
                    );
                    $connection = DB::connection('lazim_accounts');
                    $created_by = $connection->table('users')->where('owner_association_id', $flat->owner_association_id)->where('type', 'company')->first()?->id;
                    $invoiceId = $connection->table('invoices')->where('created_by', $created_by)->orderByDesc('invoice_id')->first()?->invoice_id + 1;
                    $customerId = $connection->table('customer_flat')->where('flat_id', $flat->id)->where('building_id', $buildingId)->where('active', true)->first()?->customer_id;
                    $category_id = $connection->table('product_service_categories')->where('name', 'Service Charges')->first()?->id;
                    $ref_number = random_int(11111111, 99999999);
                    $connection->table('invoices')->updateOrInsert([
                        'building_id' => $buildingId,
                        'flat_id' => $flat->id,
                        'issue_date' => $property['invoiceDate'],
                    ],[
                        'invoice_id' => $invoiceId,
                        'customer_id' => $customerId,
                        'due_date' => $property['invoiceDueDate'],
                        'send_date' => $property['invoiceDate'],
                        'category_id' => $category_id,
                        'ref_number' => $ref_number,
                        'status' => false,
                        'shipping_display' => true,
                        'discount_apply' => false,
                        'created_by' => $created_by,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $invoice = $connection->table('invoices')->where('ref_number',$ref_number)->first();
                    Log::info('invoice-------'.json_encode($invoice));
                    $product = $connection->table('product_services')->where('name', 'Service Charges' )->first();
                    if(!$product){
                        $connection->table('product_services')->insert([
                            'name' => 'Service Charges',
                            'sku' => 'SER-001',
                            'sale_price' => 0,
                            'purchase_price' => 0,
                            'quantity' => 0,
                            'tax_id' =>2,
                            'category_id' => $category_id,
                            'unit_id' => 1,
                            'type' => 'Service',
                            'created_by' => $created_by,
                        ]);
                    }
                    $product = $connection->table('product_services')->where('name', 'Service Charges' )->first();
                    Log::info('product-----'.json_encode($product));
                    $connection->table('invoice_products')->insert([
                        'invoice_id' => $invoice?->id,
                        'product_id' => $product->id,
                        'quantity' =>1,
                        'tax' =>2,
                        'price' => $property['invoiceAmount']
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch or save invoices');
        }
    }
}
