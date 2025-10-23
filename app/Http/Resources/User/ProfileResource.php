<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Models\Forms\MoveInOut;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $moveInOut = MoveInOut::where('user_id', $this->id)->latest()->first();
        $data = [
            'id'=>$this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_pic' => $this->profile_photo ? Storage::disk('s3')->url($this->profile_photo) : null,
            'email_verified'=>$this->email_verified,
            'phone_verified'=>$this->phone_verified,
            'active'=>$this->active,
            'lazim_id'=>$this->lazim_id,
            'role_id'=>$this->role_id,
            'owner_association_id'=>$this->owner_association_id,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'remember_token'=>$this->remember_token,
            'selectType'=> 'globalOa',

            // Verification fields
            'email_verified_status' => (bool) $this->email_verified,
            'mobile_verified_status' => (bool) $this->phone_verified,

            // Documents (from MoveInOut)
            'passport_verified' => $moveInOut ? (bool) $moveInOut->passport : false,
            'visa_verified' => $moveInOut ? (bool) $moveInOut->visa : false,
            'eid_verified' => $moveInOut ? (bool) $moveInOut->eid : false,
            'title_deed_or_ejari_verified' => $moveInOut ? ((bool) $moveInOut->title_deed || (bool) $moveInOut->ejari) : false,

        ];
        if($this->role->name == 'Security'){
            $data['building_id'] = $this->pocs[0]->building_id;
        }
        return $data;
    }
}
