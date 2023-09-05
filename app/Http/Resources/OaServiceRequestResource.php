<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OaServiceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'service_parameter'  => $this->serviceParameter->name,
            'property_group'        => $this->property_group,
            'from_date'             => $this->from_date,
            'to_date'               => $this->to_date,
            'status'                => $this->status,
            'property_name'         => $this->property_name,
            'service_period'         => $this->service_period,
        ];
    }
}
