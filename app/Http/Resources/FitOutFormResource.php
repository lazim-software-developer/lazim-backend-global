<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FitOutFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                           => $this->id,
            'contractor_name'              => $this->contractor_name,
            'flat_id'                      => $this->flat_id,
            'flat_number'                  => $this->flat->property_number,
            'contractor_phone'             => $this->phone,
            'contractor_email'             => $this->email,
            'no_objection'                 => $this->no_objection,
            'undertaking_of_waterproofing' => $this->undertaking_of_waterproofing,
            'building_id'                  => $this->building_id,
            'building_name'                => $this->building->name,
            'user_id'                      => $this->user_id,
            'user_name'                    => $this->user->first_name,
            'owner_association_id'         => $this->owner_association_id,
            'status'                       => $this->status,
            'remarks'                      => $this->remarks,
            'admin_document'               => $this->admin_document ? env('AWS_URL') . '/' . $this->admin_document : null,
            'ticket_number'                => $this->ticket_number,
            'payment_status'               => $this?->orders?->first()?->payment_status ?? null,
        ];
    }
}
