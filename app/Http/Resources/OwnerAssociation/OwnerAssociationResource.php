<?php

namespace App\Http\Resources\OwnerAssociation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OwnerAssociationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if(!empty($this->profile_photo))
        {
            $logo=Storage::disk('s3')->url($this->profile_photo);
        }else{
            $logo=NULL;
        }
        return[
            'id'=>$this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email, 
            'logo' => !empty($this->profile_photo) ? Storage::disk('s3')->url($this->profile_photo) : NULL,
            'trn_number' => $this->trn_number,
            'address' => $this->address,
            'mollak_id' => $this->mollak_id,
            'active' => $this->active,
            'bank_account_number' => $this->bank_account_number,
            'trade_license' => !empty($this->trade_license) ? Storage::disk('s3')->url($this->trade_license) : NULL,
            'chamber_document' => !empty($this->dubai_chamber_document) ? Storage::disk('s3')->url($this->dubai_chamber_document) : NULL,
            'memorandum_of_association' => !empty($this->memorandum_of_association) ? Storage::disk('s3')->url($this->memorandum_of_association) : NULL, 
            'trn_certificate' => !empty($this->trn_certificate) ? Storage::disk('s3')->url($this->trn_certificate) : NULL, 
            'oa_number' => $this->oa_number,                 
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }
}
