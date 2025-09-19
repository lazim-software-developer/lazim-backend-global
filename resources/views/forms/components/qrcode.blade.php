<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }" style="max-width: 100%; width: 100%; display: flex; flex-direction: column; align-items: center;">
        @if ($getRecord() != null)
            @if ($getRecord()?->floors != null || $getRecord()?->floor_id != null)
            @php
                $qrCode = $getRecord()->qr_code;
                if ($qrCode == null) {
                    $qrCodeContent = [
                        'location_id' => isset($getRecord()->code) ? $getRecord()->id : null,
                        'floors' => $getRecord()->floors ?? $getRecord()->floor_id,
                        'building_id' => $getRecord()->building_id,
                        'code' => $getRecord()->code ?? $getRecord()->floors,
                    ];
                    // Generate QR code
                    $qrCodeSize = 200; // Match the destination image width
                    $width = 200;
                    $height = $qrCodeSize + 100; // Enough space for QR code and text
                    $qrCode = SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                        ->size($qrCodeSize)
                        ->errorCorrection('H')
                        ->margin(4)
                        ->generate(json_encode($qrCodeContent));
                    $qrText[] = $qrCodeContent['code'] ?? ' ';

                    $qrImage = addTextToQR($qrCode, $qrText, $qrCodeSize, $width, $height);
                    $qrCode = $qrImage;
                    $getRecord()->qr_code =  $qrImage;
                    $getRecord()->save();
                }
            @endphp
            <div style="max-width: 200px; width: 100%;">
                <div style="width: 100%; height: auto; display: block;">
                    {!! str_replace('<svg', '<svg style="width: 100%; height: auto; display: block;"', $qrCode) !!}
                </div>
            </div>
            <div style="margin-top: 1rem; --c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600); text-align: center;">
                Scan QR Code
            </div>
            @endif
        @endif
    </div>
</x-dynamic-component>
