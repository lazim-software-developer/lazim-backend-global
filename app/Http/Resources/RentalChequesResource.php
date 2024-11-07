<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalChequesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'cheque_number'     => $this->cheque_number,
            'amount'            => $this->amount,
            'due_date'          => $this->due_date,
            'status'            => $this->status,
            'mode_payment'      => $this->mode_payment,
            'cheque_status'     => $this->cheque_status,
            'payment_link'      => $this->payment_link,
            'comments'          => $this->comments,
        ];
    }
}
