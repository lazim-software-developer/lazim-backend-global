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
            'id'                    => $this->id,
            'service_parameter_id'  => $this->service_parameter_id,
            'property_group'        => $this->property_group,
            'from_date'             => $this->from_date,
            'to_date'               => $this->to_date,
            'status'                => $this->status,
            'uploaded_by'           => $this->uploaded_by,
        ];
    }
}
