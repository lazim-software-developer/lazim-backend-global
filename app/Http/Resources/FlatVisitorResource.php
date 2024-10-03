<?php

namespace App\Http\Resources;

use App\Http\Resources\Documents\DocumentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlatVisitorResource extends JsonResource
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
            "flat_id"              => $this->flat_id,
            "flat_number"          => $this->flat->property_number,
            "building_id"          => $this->building_id,
            "building_name"        => $this->building?->name,
            "owner_association_id" => $this->owner_association_id,
            "name"                 => $this->name,
            "phone"                => $this->phone,
            "email"                => $this->email,
            "start_time"           => $this->start_time,
            "end_time"             => $this->end_time,
            "verification_code"    => $this->verification_code,
            "remarks"              => $this->remarks,
            "number_of_visitors"   => $this->number_of_visitors,
            "ticket_number"        => $this->ticket_number,
            "time_of_viewing"      => $this->time_of_viewing,
            "status"               => $this->status,
            'documents'            => $this->guestDocuments ? DocumentResource::collection($this->guestDocuments) : null,
        ];
    }
}
