<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;background-color: #F5F5F5;">
            @php
            $budget_amount = App\Models\Accounting\Budgetitem::query()
            ->where('budget_id', $getRecord()?->tender?->budget_id)
            ->where('service_id', $getRecord()?->tender?->service_id)->first()?->total;
            @endphp
            @if($budget_amount!=null)
            <div>AED {{ $budget_amount}}</div>
            @else
            <div>{{'NA'}}</div>
            @endif
        </div>
    </div>
</x-dynamic-component>