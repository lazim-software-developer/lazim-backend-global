<div>
    @php
        use App\Models\Accounting\Budgetitem;
        use App\Models\Vendor\Contract;

        $budgetItem = Budgetitem::where('budget_id', $getRecord()->id)->first();
        $serviceId = $budgetItem->service_id;
        $buildingId = $getRecord()->building_id;
        $budget = $budgetItem->budget_excl_vat;
        $amount = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->first()?->amount;


    @endphp
    {{ $budget - $amount }}
</div>
