<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\Services\ServiceResource;
use App\Models\Vendor\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buildign' => $this->building->name,
            'end_date' => $this->end_date,
            'document' => Storage::disk('s3')->url($this->document),
            'contract_type' => "AMC",
            'services' =>  ServiceResource::collection($this->services),
        ];
    }
}
