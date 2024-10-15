<?php

namespace App\Http\Resources;

use App\Http\Resources\Documents\DocumentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FitOutFormContractorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'work_type' => $this->work_type,
            'work_name' => $this->work_name,
            'status' => $this->status,
            'documents' => $this->documents()->exists() ? DocumentResource::collection($this->documents) : null,
        ];
    }
}
