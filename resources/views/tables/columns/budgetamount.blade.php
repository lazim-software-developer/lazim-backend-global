<div>
    @php
    use App\Models\Accounting\Budgetitem;

    $budget_amount = Budgetitem::query()
    ->where('budget_id', $getRecord()?->tender?->budget_id)
    ->where('service_id', $getRecord()?->tender?->service_id)->first()->total;
    @endphp
    {{ $budget_amount}}
</div>