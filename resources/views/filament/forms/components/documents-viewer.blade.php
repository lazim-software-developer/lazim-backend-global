<div class="space-y-2">
    <label class="text-sm font-medium text-gray-700">Documents</label>
    <div class="space-y-1">
        @php
            $record = $getRecord();
            $documents = $record->documents;
        @endphp

        @if($documents->isEmpty())
            <div class="text-gray-500">No documents available</div>
        @else
            @foreach($documents as $document)
                <div class="flex items-center space-x-2">
                    <a href="{{ $document->url }}"
                       target="_blank"
                       class="text-primary-600 hover:text-primary-500">
                        {{ $document->name ?: 'Document ' . ($loop->iteration) }}
                    </a>
                </div>
            @endforeach
        @endif
    </div>
</div>
 