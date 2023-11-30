<div>
@php
    use App\Models\Accounting\Budgetitem;
    use App\Models\Master\Service;

    $serviceId = Budgetitem::where('budget_id', $getRecord()->id)->first()->service_id;
    $sevice = Service::find($serviceId)

    @endphp
    {{ $sevice->code }}
</div>
