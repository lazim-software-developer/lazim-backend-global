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
        $service = Service::find($this->service_id);
        return [
            'service_id' => $this->service_id,
            'service_name' => $service->name,
        ];
    }
}
