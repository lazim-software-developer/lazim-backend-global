<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;" x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        @php
        $guestNames = json_decode($getRecord()->guest_name);
        $passportsData = json_decode($getRecord()->passport_number);
        @endphp

        @foreach($passportsData as $index => $passportData)
        @foreach($passportData as $visitorName => $passportId)
        Name: {{ $guestNames[$index]->$visitorName }} <br> Passport ID: {{ $passportId }}<br>
        @endforeach
        @endforeach

    </div>
</x-dynamic-component>