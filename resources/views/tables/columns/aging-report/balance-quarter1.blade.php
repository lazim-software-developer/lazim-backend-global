<div>
    @php
    use App\Models\Accounting\OAMInvoice;


    $lastInvoice = OAMInvoice::where(['flat_id' => $getRecord()->id])
                                ->latest('invoice_date')
                                ->first();
    $balance = $lastInvoice?->due_amount;
    $quarterAmount = $lastInvoice?->invoice_amount;
    if( $balance >= $quarterAmount ){
        $q1=$quarterAmount;
    }
    else
    {
        $q1=$balance;
    }
    @endphp
    {{round($q1, 2)}}
</div>
