<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleNocResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'unit_occupied_by'         => $this->unit_occupied_by,
            'applicant'                => $this->applicant,
            'unit_area'                => $this->unit_area,
            'sale_price'               => $this->sale_price,
            'service_charge_paid_till' => $this->service_charge_paid_till,
            'signing_authority_email'  => $this->signing_authority_email,
            'signing_authority_phone'  => $this->signing_authority_phone,
            'signing_authority_name'   => $this->signing_authority_name,
            'submit_status'            => $this->submit_status,
            'status'                   => $this->status,
            'remarks'                  => $this->remarks,
            'building_id'              => $this->building_id,
            'building_name'            => $this->building->name,
            'flat_id'                  => $this->flat_id,
            'flat_number'              => $this?->flat?->property_number,
            'user_id'                  => $this->user_id,
            'user_name'                => $this->user->first_name,
            'cooling_receipt'          => $this->cooling_receipt ? env('AWS_URL') . '/' . $this->cooling_receipt : $this->cooling_receipt,
            'cooling_soa'              => $this->cooling_soa ? env('AWS_URL') . '/' . $this->cooling_soa : $this->cooling_soa,
            'cooling_clearance'        => $this->cooling_clearance ? env('AWS_URL') . '/' . $this->cooling_clearance : $this->cooling_clearance,
            'payment_receipt'          => $this->payment_receipt ? env('AWS_URL') . '/' . $this->payment_receipt : $this->payment_receipt,
            'ticket_number'            => $this->ticket_number,
            'contacts'                 => NocContactsResource::collection($this->contacts),
            'payment_status'           => $this?->orders?->first()?->payment_status ?? null,
            'cooling_bill_paid'        => $this->cooling_bill_paid,
            'service_charge_paid'      => $this->service_charge_paid,
            'noc_fee_paid'             => $this->noc_fee_paid,
            'admin_document'           => $this->admin_document ? env('AWS_URL') . '/' . $this->admin_document : null,
        ];
    }
}
