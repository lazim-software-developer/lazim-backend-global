<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleNOCResource extends JsonResource
{
    /**
     * Transform the resource into an array for API response.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number ?? 'NA',
            'applicant' => $this->applicant ?? 'NA',
            'unit_occupied_by' => $this->unit_occupied_by ?? 'NA',
            'unit_area' => $this->unit_area ?? null,
            'sale_price' => $this->sale_price ?? null,

            'status' => $this->status ?? 'pending',
            'verified' => (bool) $this->verified,
            'remarks' => $this->remarks ?? 'NA',

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

            'signing_authority' => [
                'name' => $this->signing_authority_name ?? null,
                'email' => $this->signing_authority_email ?? null,
                'phone' => $this->signing_authority_phone ?? null,
            ],

            'payment_info' => [
                'noc_fee_paid' => (bool) $this->noc_fee_paid,
                'service_charge_paid' => (bool) $this->service_charge_paid,
                'service_charge_paid_till' => $this->service_charge_paid_till,
                'payment_link' => $this->payment_link ?? null,
            ],

            'documents' => [
                'cooling_receipt' => $this->cooling_receipt ?? null,
                'cooling_soa' => $this->cooling_soa ?? null,
                'cooling_clearance' => $this->cooling_clearance ?? null,
                'payment_receipt' => $this->payment_receipt ?? null,
                'admin_document' => $this->admin_document ?? null,
            ],

            'owner_association_id' => $this->owner_association_id,
            'user_id' => $this->user_id,
        ];
    }
}
