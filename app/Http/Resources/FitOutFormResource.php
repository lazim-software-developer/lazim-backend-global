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
            'id' => $this->id,
            'ticket_number' => $this->ticket_number ?? 'NA',
            'contractor_name' => $this->contractor_name ?? 'NA',
            'phone' => $this->phone ?? null,
            'email' => $this->email ?? null,
            'status' => $this->status ?? 'pending',
            'remarks' => $this->remarks ?? null,
            'no_objection' => (bool) $this->no_objection,
            'undertaking_of_waterproofing' => (bool) $this->undertaking_of_waterproofing,
            'payment_link' => $this->payment_link ?? null,
            'admin_document' => $this->admin_document ?? null,

            'building' => $this->whenLoaded('building', fn() => [
                'id' => $this->building->id,
                'name' => $this->building->name,
            ]),

            'flat' => $this->whenLoaded('flat', fn() => [
                'id' => $this->flat->id,
                'property_number' => $this->flat->property_number,
            ]),

            'user_id' => $this->user_id,
            'owner_association_id' => $this->owner_association_id,
        ];
    }
}
