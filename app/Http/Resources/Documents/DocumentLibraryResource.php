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
        $document = $this->documents()->where(['documentable_type' => User::class, 'document_library_id' => $this->id, 'documentable_id' => auth()->user()->id])->orderBy('id', 'desc')->first();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $document?->status,
            'url' => $document !== null ? Storage::disk('s3')->url($document?->url) : null
        ];
    }
}
