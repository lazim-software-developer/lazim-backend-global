<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <div>
            @php
            $budget_amount = App\Models\Accounting\Budgetitem::query()
            ->where('budget_id', $getRecord()?->tender?->budget_id)
            ->where('service_id', $getRecord()?->tender?->service_id)->first()?->total;
            @endphp
            @if($budget_amount!=null)
            <div>{{ $budget_amount}}</div>
            @else
            <div>{{'NA'}}</div>
            @endif
        </div>
    </div>
</x-dynamic-component>