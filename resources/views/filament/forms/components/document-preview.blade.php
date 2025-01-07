<div class="space-y-2">
    <label class="text-sm font-medium text-gray-700">Documents</label>
    <div class="grid grid-cols-2 gap-4">
        @php
            $record = $getRecord();
            $documents = $record->documents;
            $awsUrl = env('AWS_URL');
        @endphp

        @if($documents->isEmpty())
            <div class="text-gray-500">No documents available</div>
        @else
            @foreach($documents as $document)
                <div class="border rounded-lg p-4">
                    @if(str_ends_with(strtolower($document->url), '.pdf'))
                        <iframe src="{{ $awsUrl . '/' . $document->url }}"
                                class="w-full h-40 border-0">
                        </iframe>
                    @else
                        <img src="{{ $awsUrl . '/' . $document->url }}"
                             class="w-full h-40 object-cover rounded"
                             alt="View Document">
                    @endif
                    <a href="{{ $awsUrl . '/' . $document->url }}"
                       target="_blank"
                       class="mt-2 inline-block text-primary-600 hover:text-primary-500">
                        View Document
                    </a>
                </div>
            @endforeach
        @endif
    </div>
</div>
