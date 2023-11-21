<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'building_id'=>$this->building_id,
            'contract_id' => $this->contract_id,
            'invoice_number' => $this->invoice_number,
            'wda_id' => $this->wda_id,
            'date' => $this->date,
            'document' => env('AWS_URL').$this->document,
            'status' => $this->status,
            'remarks' => $this->remarks,
        ];
    }
}
