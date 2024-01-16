<div>
    @php
        use App\Models\Vendor\Contract;
        use App\Models\Accounting\Budgetitem;

        $serviceId = Budgetitem::where('budget_id', $getRecord()->id)->first()->service_id;
        $buildingId = $getRecord()->building_id;

        $amount = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->first()?->amount;
    @endphp
    {{number_format($amount,2)}}
</div>
