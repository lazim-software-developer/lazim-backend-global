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

            {{-- Sort $answerCounts in descending order based on counts --}}
            @php
            arsort($answerCounts);
            @endphp

            {{-- Iterate over sorted $answerCounts --}}
            @foreach ($answerCounts as $answer => $count)
            <div class="option">
                @php
                $optionValue = $getRecord()->options[$answer] ?? 'Unknown';
                @endphp
                <p>
                    {{ $answer }}: {{ $optionValue }} - {{ $count }} ({{ number_format(($count / $totalResponses) * 100, 2) }}%)
                </p>
                <div class="progress-bar">
                    <div class="progress" style="width: {{ ($count / $totalResponses) * 100 }}%;"></div>
                    <div class="progress-text">
                        {{ $optionValue }}
                    </div>
                </div>
            </div>
            @endforeach
            @else
            <p>NA</p>
            @endif
        </div>
    </div>
    <style>
        .option {
            margin-bottom: 15px;
        }

        .progress-bar {
            background-color: lightgray;
            height: 20px;
            border-radius: 5px;
            overflow: hidden;
            position: relative; /* Added for proper positioning of .progress-text */
        }

        .progress {
            height: 100%;
            background-color: #4caf50;
            /* green color for the progress */
        }

        .progress-text {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            color: black;
        }
    </style>
</x-dynamic-component>
