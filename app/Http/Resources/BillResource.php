<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount'   => $this->amount,
            'month'    => Carbon::parse($this->month)->format('m Y'),
            'type'     => $this->type,
            'flat_id'  => $this->flat_id,
            'due_date' => $this->due_date,
            'status'   => $this->status,
        ];
    }
}
