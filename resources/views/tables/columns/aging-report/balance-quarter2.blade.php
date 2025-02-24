<div>
@php
    use App\Models\Accounting\OAMInvoice;


    $lastInvoice = OAMInvoice::where(['flat_id' => $getRecord()->id])
                                ->latest('invoice_date')
                                ->first();
    $balance = $lastInvoice?->due_amount;
    $quarterAmount = $lastInvoice?->invoice_amount;
    if( $balance > $quarterAmount ){
        if(($balance-$quarterAmount) > $quarterAmount){
            $q2=$quarterAmount;
        }
        else
        {
            $q2 = $balance-$quarterAmount;
        }
    }
    else
    {
        $q2= 0;
    }
    @endphp
    {{ round($q2, 2) }}
</div>
