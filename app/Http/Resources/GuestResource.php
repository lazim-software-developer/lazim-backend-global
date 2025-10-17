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
            'id' => $this->id,
            'guest_name' => $this->guest_name ?? 'NA',
            'holiday_home_name' => $this->holiday_home_name ?? null,
            'passport_number' => $this->passport_number ?? null,
            'visa_validity_date' => $this->visa_validity_date,
            'stay_duration' => $this->stay_duration,
            'expiry_date' => $this->expiry_date,
            'access_card_holder' => $this->access_card_holder,
            'original_passport' => $this->original_passport,
            'guest_registration' => $this->guest_registration,
            'dtmc_license_url' => $this->dtmc_license_url,
            'emergency_contact' => $this->emergency_contact,
            'remarks' => $this->remarks,
            'status' => $this->status ?? 'pending',
            'rejected_fields' => $this->rejected_fields,
            'building' => $this->whenLoaded('building', function () {
                return [
                    'id' => $this->building->id,
                    'name' => $this->building->name,
                ];
            }),
            'flat' => $this->whenLoaded('flat', function () {
                return [
                    'id' => $this->flat->id,
                    'property_number' => $this->flat->property_number,
                ];
            }),
            'created_at' => optional($this->created_at)->toDateTimeString(),
        ];
    }
}
