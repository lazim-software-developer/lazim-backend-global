<div>
    @php
        use App\Models\Accounting\OAMReceipts;
        
        $receipts = OAMReceipts::query()
            ->where('flat_id', $getRecord()->flat_id)
            ->where('receipt_period', $getRecord()->invoice_period)
            ->pluck('receipt_amount')
            ->toArray();

        $sumOfReceipts = array_sum($receipts);
    @endphp
    {{ number_format($sumOfReceipts,2) }}
</div>
