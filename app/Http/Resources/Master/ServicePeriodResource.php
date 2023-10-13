<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class ServicePeriodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Assuming the structure of the response, adjust as needed
        return [
            'id' => $this['serviceChargePeriodId'],
            'name' => $this['periodName'],
            'from' => $this['effectiveFrom'],
            'to' => $this['effectiveTo'],
        ];
    }
}
