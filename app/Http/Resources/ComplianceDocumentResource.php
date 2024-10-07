<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplianceDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            "doc_name"    => $this->doc_name,
            "doc_type"    => $this->doc_type,
            "expiry_date" => $this->expiry_date,
            "url"         => env('AWS_URL') . '/' . $this->url,
        ];
    }
}
