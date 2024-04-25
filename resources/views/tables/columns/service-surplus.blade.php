<div>
    @php
        use App\Models\Accounting\Budgetitem;
        use App\Models\Vendor\Contract;

        $serviceId = $getRecord()->service_id;
        $buildingId = $getRecord()->budget->building_id;
        $budget = $getRecord()->budget_excl_vat;
        $contractId = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->where('end_date','>=',now()->toDateString())->first()?->id ;
        $invoiceAmount =  Invoice::where('contract_id',$contractId)->where('building_id',$buildingId)->sum('invoice_amount');
        if ($invoiceAmount){
            $balance = number_format($budget - $invoiceAmount,2);
        }
        else{
            $balance = '---';
        }

    @endphp
    {{ $balance }}
</div>
