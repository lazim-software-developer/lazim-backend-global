<?php

namespace App\Http\Resources\GateKeeper;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FloorResource extends JsonResource
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
            'building' => $this->building->name,
            'floor' => $this->floor->floors,
            'patrolled_at' => $this->patrolled_at_diff
        ];
    }
}
