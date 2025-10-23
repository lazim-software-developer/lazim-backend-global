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

            // Document verification flags
            'passport' => $this->documents->where('name', 'passport')->isNotEmpty(),
            'visa' => $this->documents->where('name', 'visa')->isNotEmpty(),
            'eid' => $this->documents->where('name', 'eid')->isNotEmpty(),
            'title_deed' => $this->documents->where('name', 'title_deed')->isNotEmpty(), 
        ];
        if($this->role->name == 'Security'){
            $data['building_id'] = $this->pocs[0]->building_id;
        }
        return $data;
    }
}
