<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" style="width:10rem">
        @if ($getRecord() != null)
            @if ($getRecord()->floors != null)
            {!! $getRecord()->qr_code !!}
                <dev 
                    style="margin-top:1rem;--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);" 
                >
                    Scan QR Code
                </dev>
            @endif
        @endif    
    </div>
</x-dynamic-component>
