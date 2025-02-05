<div class="space-y-2">
    <label class="text-sm font-medium text-gray-700">Documents</label>
    <div class="max-h-[400px] overflow-y-auto">
        <div class="grid grid-cols-2 gap-4 p-2">
            @php
                $record = $getRecord();
                $documents = $record->documents;
                $awsUrl = env('AWS_URL');
            @endphp

            @if($documents->isEmpty())
                <div class="text-gray-500 col-span-2">No documents available</div>
            @else
                @foreach($documents as $document)
                    <div class="border rounded-lg p-3 bg-white shadow-sm">
                        <div class="aspect-[4/3] overflow-hidden mb-2">
                            @if(str_ends_with(strtolower($document->url), '.pdf'))
                                <iframe src="{{ $awsUrl . '/' . $document->url }}"
                                        class="w-full h-full border-0">
                                </iframe>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                    <img src="{{ $awsUrl . '/' . $document->url }}"
                                         class="max-w-full max-h-full object-contain"
                                         alt="View Document">
                                </div>
                            @endif
                        </div>
                        <div class="text-center">
                            <a href="{{ $awsUrl . '/' . $document->url }}"
                               target="_blank"
                               class="text-primary-600 hover:text-primary-500 text-sm font-medium">
                                View Document
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
