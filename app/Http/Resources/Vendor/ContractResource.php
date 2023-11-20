<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'  => $this->id,
            'contract'=> $this->contract_type,
            'start_date' => $this->start_date,
            'end_date'=> $this->end_date,
            'url'   => env('AWS_URL').'/'.$this->document_url,

        ];
    }
}
