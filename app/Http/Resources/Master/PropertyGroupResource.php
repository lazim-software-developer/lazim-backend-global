<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyGroupResource extends JsonResource
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
            'propertyGroupId' => $this['propertyGroupId'],
            'propertyGroupName' => $this['propertyGroupName']['englishName'],
        ];
    }
}

