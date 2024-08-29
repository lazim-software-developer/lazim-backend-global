<?php

namespace App\Http\Resources\Assets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetTechniciansResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'technician_id' => $this->id,
            'technician_name' => $this->first_name,
        ];
    }
}
