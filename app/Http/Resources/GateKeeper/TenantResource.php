<?php

namespace App\Http\Resources\GateKeeper;

use App\Http\Resources\FamilyMembersResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->tenant_id,
            'flat' => $this->flat->property_number,
            'flat_id' => $this->flat_id,
            'user_name' => $this->user->first_name,
            'user_email' => $this->user->email,
            'user_phone' => $this->user->phone,
            'profile_pic' => $this->user->profile_photo ? Storage::disk('s3')->url($this->user->profile_photo) : null,
            'family_members' => FamilyMembersResource::collection($this->user->residentialForm()->where('building_id', $this->building_id)->where('status', 'approved')->get())
        ];
    }
}
