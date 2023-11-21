<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WDAResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'wda_number' => $this->wda_number,
            'date' => $this->date,
            'status' => $this->status,
            'document' => env('AWS_URL').'/'.$this->document,
            'description' => $this->job_description,
            'remarks' => $this->remarks,
        ];
    }
}
