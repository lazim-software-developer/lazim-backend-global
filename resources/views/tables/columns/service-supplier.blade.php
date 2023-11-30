<div>
    @php
    use App\Models\Vendor\Contract;
    use App\Models\Vendor\Vendor;
    use App\Models\Accounting\Budgetitem;

    $serviceId = Budgetitem::where('budget_id', $getRecord()->id)->first()->service_id;
    $buildingId = $getRecord()->building_id;

    $vendorId = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->first()?->vendor_id;
    $vendorName = Vendor::find($vendorId)?->name;
    @endphp
    {{$vendorName}}
</div>
