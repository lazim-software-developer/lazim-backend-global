<?php

namespace App\Http\Resources\Building;

use Illuminate\Http\Request;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
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
        $oaIds            = DB::table('building_owner_association')->where('building_id', $this->building->id)->pluck('owner_association_id');
        $ownerAssociation = OwnerAssociation::whereIn('id', $oaIds)->pluck('role')->unique();
        $oA               = $ownerAssociation->contains('OA');
        $propertyManager  = $ownerAssociation->contains('Property Manager');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'oa' => $oA,
            'property_manager' => $propertyManager,
            'owner_association_id' => $this->owner_association_id,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'area' => $this->area,
            'description' => $this->description,
            'floors' => $this->floors,
            'allow_postupload' => $this->allow_postupload,
            'cover_photo' => $this->cover_photo,
            'show_inhouse_services' => $this->show_inhouse_services,
            'mollak_property_id' => $this->mollak_property_id,
            'building_type' => $this->building_type,
            'parking_count' => $this->parking_count,
        ];
    }
}
