<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'flat_id'                     => $this->flat_id,
            'number_of_cheques'           => $this->number_of_cheques,
            'contract_start_date'         => $this->contract_start_date,
            'contract_end_date'           => $this->contract_end_date,
            'admin_fee'                   => $this->admin_fee,
            'other_charges'               => $this->other_charges,
            'advance_amount'              => $this->advance_amount,
            'advance_amount_payment_mode' => $this->advance_amount_payment_mode,
            'status'                      => $this->status,
            'cheques'                     => RentalChequesResource::collection($this->rentalCheques),
        ];
    }
}
