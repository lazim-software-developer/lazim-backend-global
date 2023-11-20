<?php

namespace App\Http\Resources\Assets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicianAssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->asset->name,
            'asset_id' => $this->asset->id,
            'asset_name' => $this->asset->name,
            'building_name' => $this->building?->name,
            'location' => 'sdasdas',
            'description' => 'sdsd',
            'last_service_on' => 'sdsd',
        ];
    }
}