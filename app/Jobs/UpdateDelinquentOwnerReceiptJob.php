<?php

namespace App\Jobs;

use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\DelinquentOwner;
use App\Models\FlatOwners;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateDelinquentOwnerReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $receipt)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $invoice = OAMInvoice::where(['flat_id' => $this->receipt->flat_id, 'building_id' => $this->receipt->building_id, 'invoice_period' => $this->receipt->receipt_period])->first();
            Log::info($invoice);
            if($invoice && Carbon::parse($this->receipt->receipt_date)->greaterThan(Carbon::parse($invoice->invoice_due_date))){
                preg_match('/(\d{4})/', $invoice->invoice_quarter, $matches);
                $year = $matches[1];
                $invoiceQuarter = $invoice->invoice_quarter;
                $quarterNumber = substr($invoiceQuarter, 1, 1);
                $balanceFieldName = 'quarter_' . $quarterNumber . '_balance';
                $receiptAmount = $this->receipt?->receipt_amount ?: 0;
                $delinquent = DelinquentOwner::where(['year'=> $year,
                                                        'building_id'=>$invoice->building_id,
                                                        'flat_id'=>$invoice->flat_id])->first();
                $flatId= $invoice->flat_id;
                $ownerId = FlatOwners::where('flat_id', $flatId)->where('active', 1)->first()?->owner_id;
                $lastReceipt = OAMReceipts::where(['flat_id' => $flatId])
                ->latest('receipt_date')
                ->first(['receipt_date', 'receipt_amount']);
                Log::info('last'.$lastReceipt);
                $lastInvoice = OAMInvoice::where(['flat_id' => $flatId])
                                    ->latest('invoice_date')
                                    ->first();
                $dueAmount = 0;
                if($lastInvoice?->invoice_due_date && $lastReceipt?->receipt_date && Carbon::parse($lastReceipt?->receipt_date)->greaterThan(Carbon::parse($lastInvoice?->invoice_due_date)))
                    {
                        $dueAmount = $lastInvoice->due_amount - $lastReceipt?->receipt_amount;
                    }
                if(!$delinquent){
                    $delinquent=DelinquentOwner::updateOrCreate(
                        [
                            'year'=> $year,
                            'building_id'=>$invoice->building_id,
                            'flat_id'=>$flatId,
                        ],
                        [
                            'owner_id'=>$ownerId,
                            'last_payment_date'=> $lastReceipt?->receipt_date,
                            'last_payment_amount' => $lastReceipt?->receipt_amount,
                            'outstanding_balance' => $dueAmount ,
                            'invoice_pdf_link' => $lastInvoice->invoice_pdf_link,
                        ]);
                }
                $delinquent->$balanceFieldName = $invoice->invoice_amount - $receiptAmount;
                $delinquent->save();
                $invoice->processed = true;
                $invoice->save();
            }
            $this->receipt->processed = true;
            $this->receipt->save();
            Log::info('success');
    }
}
