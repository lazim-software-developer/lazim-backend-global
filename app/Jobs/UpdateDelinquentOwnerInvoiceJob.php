<?php

namespace App\Jobs;

use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\AgingReport;
use App\Models\Building\Flat;
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

class UpdateDelinquentOwnerInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $invoice)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
            
            $receipts = OAMReceipts::where(['flat_id' => $this->invoice->flat_id,'receipt_period' => $this->invoice->invoice_period])->first();
            preg_match('/(\d{4})/', $this->invoice->invoice_quarter, $matches);
            $year = $matches[1];
            $flatId= $this->invoice->flat_id;
            $ownerId = FlatOwners::where('flat_id', $flatId)->where('active', 1)->first()?->owner_id;
            $lastReceipt = OAMReceipts::where(['flat_id' => $flatId])->where('receipt_period', 'like', '%' . $year . '%')
            ->latest('receipt_date')
            ->first(['receipt_date', 'receipt_amount']);
            $lastInvoice = OAMInvoice::where(['flat_id' => $flatId])
                                ->where('invoice_period', 'like', '%' . $year . '%')
                                ->latest('invoice_date')
                                ->first();
            $dueAmount = $lastInvoice->due_amount;
            $oa_id = Flat::find($flatId)?->owner_association_id;
            if(!$receipts || $this->invoice->invoice_due_date < Carbon::parse($receipts?->receipt_date)->toDateString()){
                
                if($lastInvoice?->invoice_due_date && $lastReceipt?->receipt_date && Carbon::parse($lastReceipt?->receipt_date)->greaterThan(Carbon::parse($lastInvoice?->invoice_due_date)))
                    {
                        $dueAmount = $lastInvoice->due_amount - $lastReceipt?->receipt_amount;
                    }
                $delinquent=DelinquentOwner::updateOrCreate(
                    [
                        'year'=> $year,
                        'building_id'=>$this->invoice->building_id,
                        'flat_id'=>$flatId,
                        // 'owner_association_id' => $oa_id
                    ],
                    [
                        'owner_id'=>$ownerId,
                        'last_payment_date'=> $lastReceipt?->receipt_date,
                        'last_payment_amount' => $lastReceipt?->receipt_amount,
                        'outstanding_balance' => $dueAmount ,
                        'invoice_pdf_link' => $lastInvoice->invoice_pdf_link,
                    ]);
                $invoiceQuarter = $this->invoice->invoice_quarter;
                $quarterNumber = substr($invoiceQuarter, 1, 1);
                $balanceFieldName = 'quarter_' . $quarterNumber . '_balance';
                $receiptAmount = $receipts?->receipt_amount ?: 0;
                $delinquent->$balanceFieldName = $this->invoice->previous_balance  + $this->invoice->invoice_amount - $receiptAmount;
                $delinquent->save();
                if($receipts){
                    $receipts->processed = true;
                    $receipts->save();
                }
            }

            $aging = AgingReport::updateOrCreate([
                        'year'=> $year,
                        'building_id'=>$this->invoice->building_id,
                        'flat_id'=>$flatId,
                        // 'owner_association_id' => $oa_id
                    ],
                    [
                        'owner_id'=>$ownerId,
                        'outstanding_balance' => $dueAmount,
                    ]);
            $balances = ['balance_1', 'balance_2', 'balance_3', 'balance_4', 'over_balance'];
            $multiplier = 0;
            
            foreach ($balances as $balance) {
                if ($balance == 'over_balance') {
                    // Special case for over_balance
                    if (($dueAmount - (4 * $lastInvoice->invoice_amount)) >= $lastInvoice->invoice_amount) {
                        $aging->over_balance = $dueAmount;
                    } else {
                        $aging->over_balance = max($dueAmount - (4 * $lastInvoice->invoice_amount), 0);
                    }
                } else {
                    // General case for balance_1, balance_2, balance_3, balance_4
                    $difference = $dueAmount - ($multiplier * $lastInvoice->invoice_amount);
                    $aging->$balance = $difference >= $lastInvoice->invoice_amount ? $lastInvoice->invoice_amount : max($difference, 0);
                }
                $aging->save();
                $multiplier++;
            }
                    

            if($this->invoice->invoice_due_date < Carbon::now()->toDateString()){
                $this->invoice->processed = true;
                $this->invoice->save();
            }
    }
}
