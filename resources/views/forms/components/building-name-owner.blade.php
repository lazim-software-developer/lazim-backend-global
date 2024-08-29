<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div>
        {{ $getRecord()->flat->building->name}}
    </div>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
    </div>
</x-dynamic-component>