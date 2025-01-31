<?php

namespace App\Http\Resources\Documents;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class DocumentLibraryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Determine if the document is "Title deed"
        if (in_array($this->name, ['Title deed','Makani number','Unit plan'])) {
            // Fetch the "Title deed" document based on flat_id
            $flatId   = $request->get('flat_id'); // Assuming flat_id is passed in the request
            $document = $this->documents()
                ->where([
                    'documentable_type'   => User::class,
                    'document_library_id' => $this->id,
                    'documentable_id'     => auth()->user()->id,
                    'flat_id'             => $flatId,
                ])
                ->orderBy('id', 'desc')
                ->first();
        } else {
            // Fetch other documents normally
            $document = $this->documents()
                ->where([
                    'documentable_type'   => User::class,
                    'document_library_id' => $this->id,
                    'documentable_id'     => auth()->user()->id,
                ])
                ->orderBy('id', 'desc')
                ->first();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $document?->status,
            'remarks' => $document?->remarks,
            'url' => $document !== null ? ($this->name != 'Makani number' ? Storage::disk('s3')->url($document?->url) : $document?->url) : null
        ];
    }
}
