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
        $billNumber = match($this->type) {
            'DEWA' => $this->flat->dewa_number,
            'BTU' => $this->flat->{'btu/ac_number'},
            'lpg' => $this->flat->lpg_number,
            'Telecommunication' => $this->flat->{'etisalat/du_number'},
            default => null
        };

        return [
            'amount'   => $this->amount,
            'month'    => Carbon::parse($this->month)->format('m-Y'),
            'type'     => $this->type,
            'bill_number' => $billNumber,
            'flat_id'  => $this->flat_id,
            'due_date' => $this->due_date,
            'status'   => $this->status,
        ];
    }
}
