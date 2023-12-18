<?php

namespace App\Http\Resources\Forms;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorResource extends JsonResource
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
            'flat' => $this->flat->property_number,
            'name' => $this->name,
            'email' => $this->email,
            'date' => $this->start_time->format('d-m-Y'),
            'time' => Carbon::parse($this->time_of_viewing)->format('h:i A'),
            'no_of_visitors' => $this->number_of_visitors
        ];
    }
}
