<?php

namespace App\Http\Resources\Asset;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'                 => $this->name,
            'location'             => $this->location,
            'description'          => $this->description,
            // 'building_id'          => $this->building_id,
            // 'service_id'           => $this->service_id,
            'floor'                => $this->floor,
            'division'             => $this->division,
            'discipline'           => $this->discipline,
            'frequency_of_service' => $this->frequency_of_service,
            'qr_code'              => $this->qr_code ?? null,
            'asset_code'           => $this->asset_code,
        ];
    }
}
