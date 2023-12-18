<div>
@php
    use App\Models\Accounting\OAMInvoice;


    $lastInvoice = OAMInvoice::where(['flat_id' => $getRecord()->id])
                                ->latest('invoice_date')
                                ->first();
    $balance = $lastInvoice?->due_amount;
    $quarterAmount = $lastInvoice?->invoice_amount;
    if( $balance >= ($quarterAmount*3) ){
        if(($balance-($quarterAmount *3)) > $quarterAmount){
            $q4=$quarterAmount;
        }
        else
        {
            $q4= $balance-($quarterAmount *3);
        }
    }
    else
    {
        $q4= 0 ;
    }
    @endphp
    {{ round($q4, 2) }}
</div>
