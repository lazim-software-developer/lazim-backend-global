<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoveInOutResource extends JsonResource
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
            'name'                 => $this->name,
            'email'                => $this->email,
            'phone'                => $this->phone,
            'type'                 => $this->type,
            'moving_date'          => $this->moving_date,
            'moving_time'          => $this->moving_time,
            'flat'                 => $this->flat?->property_number,
            'building_name'        => $this->building?->name,
            'status'               => $this->status,
            'remarks'              => $this->remarks,
            'ticket_number'        => $this->ticket_number,
            'handover_acceptance'  => $this->handover_acceptance ? env('AWS_URL') . '/' . $this->handover_acceptance : null,
            'receipt_charges'      => $this->receipt_charges ? env('AWS_URL') . '/' . $this->receipt_charges : null,
            'contract'             => $this->contract ? env('AWS_URL') . '/' . $this->contract : null,
            'title_deed'           => $this->title_deed ? env('AWS_URL') . '/' . $this->title_deed : null,
            'passport'             => $this->passport ? env('AWS_URL') . '/' . $this->passport : null,
            'dewa'                 => $this->dewa ? env('AWS_URL') . '/' . $this->dewa : null,
            'cooling_registration' => $this->cooling_registration ? env('AWS_URL') . '/' . $this->cooling_registration : null,
            'gas_registration'     => $this->gas_registration ? env('AWS_URL') . '/' . $this->gas_registration : null,
            'vehicle_registration' => $this->vehicle_registration ? env('AWS_URL') . '/' . $this->vehicle_registration : null,
            'movers_license'       => $this->movers_license ? env('AWS_URL') . '/' . $this->movers_license : null,
            'movers_liability'     => $this->movers_liability ? env('AWS_URL') . '/' . $this->movers_liability : null,
            'noc_landlord'         => $this->noc_landlord ? env('AWS_URL') . '/' . $this->noc_landlord : null,
            'cooling_final'        => $this->cooling_final ? env('AWS_URL') . '/' . $this->cooling_final : null,
            'gas_final'            => $this->gas_final ? env('AWS_URL') . '/' . $this->gas_final : null,
            'cooling_clearance'    => $this->cooling_clearance ? env('AWS_URL') . '/' . $this->cooling_clearance : null,
            'gas_clearance'        => $this->gas_clearance ? env('AWS_URL') . '/' . $this->gas_clearance : null,
            'dewa_final'           => $this->dewa_final ? env('AWS_URL') . '/' . $this->dewa_final : null,
        ];
    }
}
