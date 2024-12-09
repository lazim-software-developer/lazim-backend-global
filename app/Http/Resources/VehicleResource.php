<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user_name' => $this->user->first_name,
            'flat_id' => $this->flat_id,
            'flat_number' => $this->flat->property_number,
            'parking_number' => $this->parking_number,
            'vehicle_number' => $this->vehicle_number,
            'owner_association_id' => $this->owner_association_id,
        ];
    }
}
