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
        $today = Carbon::now();

        $daysRemaining = $today->diffInDays($endDate);
        $status = $endDate->isPast() ? 'expired' : 'active';
        $daysRemaining = $status === 'expired' ? 0 : $daysRemaining;
        return [
            'id'  => $this->id,
            'contract'=> $this->service->name.' - '.$this->contract_type,
            'start_date' => $startDate->format('Y-m-d'), 
            'end_date' => $endDate->format('Y-m-d'), 
            'days_remaining' => $daysRemaining,
            'status' => $status,
            'url'   => $this->document_url ? env('AWS_URL').'/'.$this->document_url : null,

        ];
    }
}
