<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResidentialFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"                   => $this->id,
            "unit_occupied_by"     => $this->unit_occupied_by,
            "name"                 => $this->name,
            "building_id"          => $this->building_id,
            "building_name"        => $this->building->name,
            "flat_id"              => $this->flat_id,
            "flat_number"          => $this->flat->property_number,
            "passport_number"      => $this->passport_number,
            "number_of_adults"     => $this->number_of_adults,
            "number_of_children"   => $this->number_of_children,
            "office_number"        => $this->office_number,
            "trn_number"           => $this->trn_number,
            "passport_expires_on"  => $this->passport_expires_on,
            "emirates_id"          => $this->emirates_id,
            "remarks"              => $this->remarks,
            "status"               => $this->status,
            "emirates_expires_on"  => $this->emirates_expires_on,
            "title_deed_number"    => $this->title_deed_number,
            "user_id"              => $this->user_id,
            "user_name"            => $this->user->first_name,
            "emergency_contact"    => $this->emergency_contact ? json_decode($this->emergency_contact) : null,
            "passport_url"         => $this->passport_url ? env('AWS_URL') . '/' . $this->passport_url : $this->passport_url,
            "emirates_url"         => $this->emirates_url ? env('AWS_URL') . '/' . $this->emirates_url : $this->emirates_url,
            "title_deed_url"       => $this->title_deed_url ? env('AWS_URL') . '/' . $this->title_deed_url : $this->title_deed_url,
            "owner_association_id" => $this->owner_association_id,
            "tenancy_contract"     => $this->tenancy_contract ? env('AWS_URL') . '/' . $this->tenancy_contract : $this->tenancy_contract,
            "ticket_number"        => $this->ticket_number,
        ];
    }
}
