<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }">
        <!-- Interact with the `state` property in Alpine.js -->
        <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;background-color: #F5F5F5;">
            <h3>Before Service Comment:-</h3>
            <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;background-color: #F5F5F5;">
                {{json_decode($getRecord()->comment)->before ? json_decode($getRecord()->comment)->before : 'NA'}}
            </div>
            <h3>Image:-</h3>
            <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;background-color: #F5F5F5; display: flex; justify-content: center; align-items: center;">
                <img src="https://lazim-dev.s3.ap-south-1.amazonaws.com/{{json_decode($getRecord()->media)->before}}" alt="Before Service" style="max-width: 100%; height: 250px;"/><br>
            </div>
        </div>
        @if(json_decode($getRecord()->comment)->after != null)
        <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;background-color: #F5F5F5;">
            <h3>After Service Comment:-</h3>
            <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;background-color: #F5F5F5;">
                {{json_decode($getRecord()->comment)->after ? json_decode($getRecord()->comment)->after : 'NA'}}
            </div>
            <h3>Image:-</h3>
            <div style="border: 1.5px lightgray solid; padding: 7px; border-radius: 8px;background-color: #F5F5F5; display: flex; justify-content: center; align-items: center;">
                <img src="https://lazim-dev.s3.ap-south-1.amazonaws.com/{{json_decode($getRecord()->media)->after}}" alt="After Service" style="max-width: 100%; height: 250px;"/><br>
            </div>
        </div>
        @endif
    </div>
</x-dynamic-component>
