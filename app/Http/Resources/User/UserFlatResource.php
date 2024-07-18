<?php

namespace App\Http\Resources\User;

use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use Illuminate\Http\Request;
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
        return [
            'flat_name' => $this->property_number,
            'flat_id' => $this->id,
            'building_name' => $this->building->name,
            'building_slug' => $this->building->slug,
            'building_id' => $this->building->id,
            'role' => $flat?->role,
            'oa_logo' => env('AWS_URL').'/'.$flatId->ownerAssociation?->profile_photo,
            'building_logo' => $this->building->cover_photo? env('AWS_URL').'/'.$this->building->cover_photo : null,
        ];
    }
}
