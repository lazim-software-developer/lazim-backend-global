<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "amount" => $this->amount,
            "submitted_on" => $this->submitted_on,
            "status" => $this->status,
            "remarks" => $this->remarks,
            "service" => $this->tender->service->name,
            "document" => env('AWS_URL').'/'.$this->document,
        ];
    }
}
