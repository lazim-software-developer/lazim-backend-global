<?php

namespace App\Http\Resources\Building;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class BuildingResource extends JsonResource
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
            'slug' => $this->slug,
            'merchant_code' => $this->merchant_code,
            'property_group_id' => $this->property_group_id,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'area' => $this->area,
            'city_id' => $this->city_id,
            'lat' => $this->lat,
            'floors' => $this->floors,
            'owner_association_id' => $this->owner_association_id,
            'allow_postupload' => $this->allow_postupload,
            'show_inhouse_services' => $this->show_inhouse_services,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'cover_photo' => !empty($this->cover_photo) ? Storage::disk('s3')->url($this->cover_photo) : NULL,
            'description' => $this->description
        ];
    }
}
