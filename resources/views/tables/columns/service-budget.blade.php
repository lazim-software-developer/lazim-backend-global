<div>
@php
    use App\Models\Accounting\Budgetitem;

    $budget = Budgetitem::where('budget_id', $getRecord()->id)->first()->budget_excl_vat;

    @endphp
    
    {{ number_format($budget,2) }}
</div>
