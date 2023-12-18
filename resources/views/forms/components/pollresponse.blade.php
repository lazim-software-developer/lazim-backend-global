<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <div style="border: 1.5px lightgray solid;padding:7px;border-radius: 8px;">
            {{ $getRecord()->responses}}
        </div>
    </div>
</x-dynamic-component>
