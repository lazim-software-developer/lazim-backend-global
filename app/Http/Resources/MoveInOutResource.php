<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoveInOutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'type' => $this->type,
            'moving_date' => $this->moving_date,
            'moving_time' => $this->moving_time,
            'flat' => $this->flat?->property_number
        ];
    }
}
