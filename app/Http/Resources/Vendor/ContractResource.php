<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        return [
            'id'  => $this->id,
            'contract'=> $this->contract_type,
            'start_date' => $startDate->format('Y-m-d'), 
            'end_date' => $endDate->format('Y-m-d'), 
            'days_remaining' => Carbon::now()->diffInDays($endDate),
            'url'   => env('AWS_URL').'/'.$this->document_url,

        ];
    }
}
