<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
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
            "passport_number"      => $this->passport_number,
            "guest_name"           => $this->guest_name,
            "visa_validity_date"   => $this->visa_validity_date,
            "stay_duration"        => $this->stay_duration,
            "dtmc_license_url"     => $this->dtmc_license_url ? env('AWS_URL') . '/' . $this->dtmc_license_url : $this->dtmc_license_url,
            "remarks"              => $this->remarks,
            "status"               => $this->status,
            "access_card_holder"   => $this->access_card_holder,
            "original_passport"    => $this->original_passport,
            "guest_registration"   => $this->guest_registration,
            "owner_association_id" => $this->owner_association_id,
            "holiday_home_name"    => $this->holiday_home_name,
            "emergency_contact"    => $this->emergency_contact,
            'flat_visitor'         => $this->flatVisitor ? FlatVisitorResource::make($this->flatVisitor) : null,
        ];
    }
}
