<?php

namespace App\Http\Resources\AccessCard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccessCardDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'card_type'            => $this->card_type,
            'mobile'               => $this->mobile,
            'email'                => $this->email,
            'parking_details'      => $this->parking_details,
            'tenancy'              => $this->tenancy ? env('AWS_URL') . '/' . $this->tenancy : $this->tenancy,
            'vehicle_registration' => $this->vehicle_registration ? env('AWS_URL') . '/' . $this->vehicle_registration : $this->vehicle_registration,
            'flat_id'              => $this->flat_id,
            'flat_number'          => $this->flat->property_number,
            'user_id'              => $this->user_id,
            'user_name'            => $this->user->first_name,
            'building_id'          => $this->building_id,
            'building_name'        => $this->building->name,
            'status'               => $this->status,
            'reason'               => $this->reason,
            'remarks'              => $this->remarks,
            'title_deed'           => $this->title_deed ? env('AWS_URL') . '/' . $this->title_deed : $this->title_deed,
            'passport'             => $this->passport ? env('AWS_URL') . '/' . $this->passport : $this->passport,
            'payment_status'       => $this->payment_status === 'NA' ? null : $this->payment_status,
            'ticket_number'        => $this->ticket_number,
            'created_at'           => $this->created_at,
            'emirate_of_registration'           => $this->emirate_of_registration,
        ];
    }
}
