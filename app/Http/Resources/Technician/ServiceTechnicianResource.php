<?php

namespace App\Http\Resources\Technician;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceTechnicianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "service_id" => $this->pivot->service_id,
            "vendor_id" => $this->vendor->id,
            "technician_id" => $this->technician_id,
            "technician_name" => $this->user->first_name,
            "technician_email" => $this->user->email,
            "technician_phone" => $this->user->phone,
            "technician_position" => $this->position,
            "active" => $this->active,
        ];
    }
}
