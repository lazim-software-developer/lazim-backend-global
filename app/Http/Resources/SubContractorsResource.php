<?php

namespace App\Http\Resources;

use App\Http\Resources\Services\ServiceResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubContractorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'company_name'     => $this->company_name,
            'trn_no'           => $this->trn_no,
            'service_provided' => $this->service_provided,
            'start_date'       => Carbon::parse($this->start_date)->format('m-d-Y'),
            'end_date'         => Carbon::parse($this->end_date)->format('m-d-Y'),
            'trade_licence'    => env('AWS_URL') . '/' .$this->trade_licence,
            'contract_paper'   => env('AWS_URL') . '/' .$this->contract_paper,
            'agreement_letter' => env('AWS_URL') . '/' .$this->agreement_letter,
            'additional_doc'   => $this->additional_doc ? env('AWS_URL') . '/' .$this->additional_doc : null,
            'vendor_id'        => $this->vendor_id,
            'expired'          => Carbon::parse($this->end_date)->format('m-d-Y') <= now()->format('m-d-Y'),
            'active'           => $this->active,
            'services'         => ServiceResource::collection($this->services),
        ];
    }
}
