<?php

namespace App\Http\Resources\Building;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlatResource extends JsonResource
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
            'floor' => $this->floor,
            'name' => $this->property_number,
            'building' => $this->building->name,
            'building_id' => $this->building->id,
            'owner_association_name' => $this->ownerAssociation->name,
            'owner_association_id' => $this->ownerAssociation->id,
            'description' => $this->description,
            'mollak_property_id' => $this->mollak_property_id,
            'property_type' => $this->property_type,
            'suit_area' => $this->suit_area,
            'actual_area' => $this->actual_area,
            'balcony_area' => $this->balcony_area,
            'applicable_area' => $this->applicable_area,
            'virtual_account_number' => $this->virtual_account_number,
            'parking_count' => $this->parking_count,
            'plot_number' => $this->plot_number,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'property_manager' => $this->whenLoaded('ownerAssociation', function () {
                return $this->ownerAssociation?->role === 'Property Manager';
            }, false),
        ];
    }
}
