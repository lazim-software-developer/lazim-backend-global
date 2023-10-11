<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'propertyId' => $this['mollakPropertyId'],
            'plotNumber' => $this['plotNumber'],
            'buildingName' => $this['building']['englishName'],
            'unitNumber' => $this['unitNumber']
        ];
    }
}
