<?php

namespace App\Http\Resources\Vendor;

use App\Models\Master\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SelectServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'service_id' => $this->id,
            'service_name' => $this->name,
        ];
    }
}
