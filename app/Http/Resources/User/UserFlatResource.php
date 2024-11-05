<?php

namespace App\Http\Resources\User;

use App\Models\OwnerAssociation;
use Illuminate\Http\Request;
use App\Models\Building\Flat;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFlatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $flat = FlatTenant::where(['flat_id' => $this->id, 'tenant_id' => auth()->user()->id])->first();
        $flatId = Flat::find($this->id);
        $oaIds = DB::table('building_owner_association')->where('building_id',$this->building->id)->pluck('owner_association_id');
        $ownerAssociation = OwnerAssociation::whereIn('id',$oaIds)->pluck('role')->unique();
        $oA = $ownerAssociation->contains('OA');
        $propertyManager = $ownerAssociation->contains('Property Manager');
        return [
            'flat_name' => $this->property_number,
            'flat_id' => $this->id,
            'building_name' => $this->building->name,
            'building_slug' => $this->building->slug,
            'building_id' => $this->building->id,
            'oa' => $oA,
            'propertymanager' => $propertyManager,
            'role' => $flat?->role,
            'oa_logo' => $flatId->ownerAssociation?->profile_photo ? env('AWS_URL').'/'.$flatId->ownerAssociation?->profile_photo : null,
            'building_logo' => $this->building->cover_photo ? env('AWS_URL').'/'.$this->building->cover_photo : null,
        ];
    }
}
