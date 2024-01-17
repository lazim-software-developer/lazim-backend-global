<div>
    @php
        use App\Models\Accounting\Budgetitem;
        use App\Models\Vendor\Contract;

        $serviceId = $getRecord()->service_id;
        $buildingId = $getRecord()->budget->building_id;
        $budget = $getRecord()->budget_excl_vat;
        $amount = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->first()?->amount;
        if ($amount){
            $balance = number_format($budget - $amount,2);
        }
        else{
            $balance = '---';
        }

    @endphp
    {{ $balance }}
</div>
