<div>
    @php
        use App\Models\Vendor\Contract;
        use App\Models\Accounting\Budgetitem;
        use App\Models\Accounting\Invoice;

        $serviceId = $getRecord()->service_id;
        $buildingId = $getRecord()->budget->building_id;

        $contractId = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->where('end_date','>=',now()->toDateString())->first()?->id ;
        $invoiceAmount =  Invoice::where('contract_id',$contractId)->where('building_id',$buildingId)->sum('invoice_amount');
        if($invoiceAmount){
            $invoiceAmount = number_format($invoiceAmount,2);
        }
        else{
            $invoiceAmount = 'N/A';
        }
    @endphp
    {{ $invoiceAmount }}
</div>
