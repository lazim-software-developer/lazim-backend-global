<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" style="width:10rem">
        @if ($getRecord() != null)
            @if ($getRecord()?->floors != null || $getRecord()?->floor_id != null)
            @php
                $qrCode = $getRecord()->qr_code;
                if ($qrCode == null) {
                    $qrCode = SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate(
                        json_encode([
                            'floors' => $getRecord()->floors,
                            'building_id' => $getRecord()->building_id,
                        ])
                    );
                    $getRecord()->qr_code =  $qrCode;
                    $getRecord()->save();

                }
            @endphp
            {!! $qrCode !!}
                <dev
                    style="margin-top:1rem;--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                >
                    Scan QR Code
                </dev>
            @endif
        @endif
    </div>
</x-dynamic-component>
