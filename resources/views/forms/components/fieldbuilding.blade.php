<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <div style="border: 1.5px lightgray solid;padding:7px;border-radius: 8px;">
            @php
            $images = $getRecord()->flatVisitor->guestDocuments->where('documentable_type','App\Models\Visitor\FlatVisitor')->pluck('url');
            @endphp
            @foreach($images as $image)
                <a href="https://lazim-dev.s3.ap-south-1.amazonaws.com/{{$image}}" target="_blank" style="color:blue;">View Passport</a><br>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
