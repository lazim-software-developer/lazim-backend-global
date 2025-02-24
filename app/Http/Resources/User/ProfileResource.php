<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
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
        ];
    }
}
