<?php

namespace App\Http\Resources\Facility;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacilityBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'facility_name' => $this->bookable->name,
            'facility_id' => $this->bookable->id,
            'facility_icon' => $this->bookable->icon,
            'date' => Carbon::parse($this->date)->format('jS M Y'),
            'start_time' => Carbon::parse($this->start_time)->format('ha'),
            'end_time' => Carbon::parse($this->end_time)->format('ha'),
            'approved' =>(bool) $this->approved
        ];
    }
}
