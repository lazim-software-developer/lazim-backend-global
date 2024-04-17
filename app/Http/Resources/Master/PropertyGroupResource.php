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
    // public function toArray($request)
    // {
    //     return [
    //         'id' => $this['propertyGroupId'],
    //         'name' => $this['propertyGroupName']['englishName'],
    //     ];
    // }

    public function toArray($request)
    {
        return [
            'id' => $this->resource['propertyGroupId'],
            'name' => $this->getName(), // Assuming you've added a method `getName`
        ];
    }

    public function getName()
    {
        return $this->resource['propertyGroupName']['englishName'] ?? 'Default Name'; 
    }
}

