<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;">

            @if($getRecord())
            {{-- Initialize an array to store answer counts --}}
            @php
            $answerCounts = [];
            @endphp

            {{-- Count occurrences of each answer --}}
            @foreach ($getRecord()->responses as $option)
            @php
            $answer = $option['answer'];
            $answerCounts[$answer] = isset($answerCounts[$answer]) ? $answerCounts[$answer] + 1 : 1;
            @endphp
            @endforeach

            {{-- Calculate and display percentages --}}
            @php
            $totalResponses = count($getRecord()->responses);
            @endphp

            @foreach ($answerCounts as $answer => $count)
            <p>
                {{ $answer }}: {{ $count }} ({{ number_format(($count / $totalResponses) * 100, 2) }}%)
            </p>
            @endforeach
            @else
            <p>NA</p>
            @endif
        </div>
    </div>
</x-dynamic-component>