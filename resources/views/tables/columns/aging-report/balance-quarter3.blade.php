<div>
@php
    use App\Models\Accounting\OAMInvoice;


    $lastInvoice = OAMInvoice::where(['flat_id' => $getRecord()->id])
                                ->latest('invoice_date')
                                ->first();
    $balance = $lastInvoice?->due_amount;
    $quarterAmount = $lastInvoice?->invoice_amount;
    if( $balance > ($quarterAmount*2) ){
        if(($balance-($quarterAmount *2)) > $quarterAmount){
            $q3=$quarterAmount;
        }
        else
        {
            $q3= $balance-($quarterAmount *2);
        }
    }
    else
    {
        $q3= 0 ;
    }
    @endphp
    {{ round($q3, 2) }}
</div>
