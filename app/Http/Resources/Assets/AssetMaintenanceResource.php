<?php

namespace App\Http\Resources\Assets;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetMaintenanceResource extends JsonResource
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
            'maintenance_date' => $this->maintenance_date_diff,
            'comment' => json_decode($this->comment, true),
            'media' => json_decode($this->media, true),
            'status' => $this->status,
            'building' => $this->building->name,
            'imageBaseURL' => env('AWS_URL', '')
        ];
    }
}
