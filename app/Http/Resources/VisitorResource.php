<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
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
            'name' => $this->name ?? 'NA',
            'type' => $this->type ?? 'guest',
            'status' => $this->status ?? 'pending',
            'remarks' => $this->remarks ?? null,
            'building_id' => $this->building_id,
            'flat_id' => $this->flat_id,
            'approved_by' => $this->approved_by,
            'owner_association_id' => $this->owner_association_id,
        ];
    }
}
