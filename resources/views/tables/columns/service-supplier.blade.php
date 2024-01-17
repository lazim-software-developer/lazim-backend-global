<div>
    @php
    use App\Models\Vendor\Contract;
    use App\Models\Vendor\Vendor;
    use App\Models\Accounting\Budgetitem;

    $serviceId = $getRecord()->service_id;
    $buildingId = $getRecord()->budget->building_id;

    $vendorId = Contract::where('service_id',$serviceId)->where('building_id',$buildingId)->first()?->vendor_id;
    $vendorName = Vendor::find($vendorId)?->name ;
    $vendorName = $vendorName ? $vendorName : 'N/A';
    @endphp
    {{$vendorName}}
</div>
