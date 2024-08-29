<div>
@php
    use App\Models\Accounting\OAMInvoice;


    $lastInvoice = OAMInvoice::where(['flat_id' => $getRecord()->id])
                                ->latest('invoice_date')
                                ->first();
    @endphp
{{ $lastInvoice->due_amount }}
</div>
