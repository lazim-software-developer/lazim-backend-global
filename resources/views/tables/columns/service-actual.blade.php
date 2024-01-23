<div>
    @php
        use App\Models\Vendor\Contract;
        use App\Models\Accounting\Budgetitem;

        $serviceId = $getRecord()->service_id;
        $buildingId = $getRecord()->budget->building_id;

        $amount = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->first()?->amount  ;
        if($amount){
            $amount = number_format($amount,2);
        }
        else{
            $amount = 'N/A';
        }
    @endphp
    {{ $amount }}
</div>
