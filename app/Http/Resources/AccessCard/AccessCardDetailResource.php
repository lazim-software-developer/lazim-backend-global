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
            'flat_id'              => $this->flat_id,
            'flat_number'          => $this->flat->property_number,
            'user_id'              => $this->user_id,
            'user_name'            => $this->user->first_name,
            'building_id'          => $this->building_id,
            'building_name'        => $this->building->name,
            'status'               => $this->status,
            'ticket_number'        => $this->ticket_number,
            'created_at'           => $this->created_at,
        ];
    }
}
