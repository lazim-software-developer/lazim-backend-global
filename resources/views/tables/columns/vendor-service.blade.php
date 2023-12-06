<div>
    @if($getRecord()->services->count() > 0)
    {{-- Show icon or perform other actions --}}
    <x-filament::badge color="success" icon="heroicon-m-hand-thumb-up" icon-position="after">
        Available
    </x-filament::badge>
    <!-- Replace this with the appropriate icon HTML or class -->
    @else
    {{-- No related services, display alternative content or do nothing --}}
    <x-filament::badge color="warning" icon="heroicon-m-hand-thumb-down" icon-position="after">
        Not Available
    </x-filament::badge>
    @endif
</div>