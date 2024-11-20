<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Documents\DocumentResource;

class FamilyMemberDetailsResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'passport_number' => $this->passport_number,
            'passport_expiry_date' => $this->passport_expiry_date,
            'emirates_id' => $this->emirates_id,
            'emirates_expiry_date' => $this->emirates_expiry_date,
            'gender' => $this->gender,
            'relation' => $this->relation,
            'visa_number' => $this->visa_number,
            'visa_number_expiry_date' => $this->visa_number_expiry_date,
            'documents' => DocumentResource::collection($this->documents),
        ];
    }
}
