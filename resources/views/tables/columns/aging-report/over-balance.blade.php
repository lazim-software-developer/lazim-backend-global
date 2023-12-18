<div>
@php
    use App\Models\Accounting\OAMInvoice;


    $lastInvoice = OAMInvoice::where(['flat_id' => $getRecord()->id])
                                ->latest('invoice_date')
                                ->first();
    $balance = $lastInvoice?->due_amount;
    $quarterAmount = $lastInvoice?->invoice_amount;
    if( $balance >= ($quarterAmount*4) ){
        $overBalance=$balance-($quarterAmount*4);
    }
    else
    {
        $overBalance=0;
    }
    @endphp
    {{round($overBalance, 2)}}
</div>
