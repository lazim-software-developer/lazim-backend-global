<?php

namespace App\Http\Resources\Documents;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;


class DocumentLibraryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tenantId = $this->additional['tenant_id'] ?? auth()->user()->id;

        $flatId   = $request->get('flat_id'); // Assuming flat_id is passed in the request
        // Determine if the document is "Title deed"
        if (in_array($this->name, ['Title deed','Makani number','Unit plan'])) {
            // Fetch the "Title deed" document based on flat_id
            $document = $this->documents()
                ->where([
                    'documentable_type'   => User::class,
                    'document_library_id' => $this->id,
                    'documentable_id'     => $tenantId,
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
                    'documentable_id'     => $tenantId,
                ])
                ->when($this->additional(['tenant_id']), function ($query) use ($flatId) {
                    return $query->where('flat_id', $flatId);
                })
                ->orderBy('id', 'desc')
                ->first();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $document?->status,
            'remarks' => $document?->remarks,
            'url' => $document?->url !== null ? ($this->name != 'Makani number' ? Storage::disk('s3')->url($document?->url) : $document?->url) : null
        ];
    }
}
