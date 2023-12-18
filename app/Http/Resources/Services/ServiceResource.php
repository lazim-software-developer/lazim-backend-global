<?php

namespace App\Http\Resources\Services;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'building_id'=> $this->building_id,
            'icon'=> env('AWS_URL').'/'.$this->icon,
            'active'=> $this->active,
            'subcategory_id'=> $this->subcategory_id,
            'custom'=> $this->custom,
            'owner_association_id'=> $this->owner_association_id,
            'code'=> $this->code,
        ];
    }
}
