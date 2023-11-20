<?php

namespace App\Http\Resources\Technician;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $priority = 'Low';

        if($this->priority === 1) {
            $priority = 'High';
        } else if($this->priority == 2) {
            $priority = 'Medium';
        }

        return [
            'id' => $this->id,
            'building' => $this->building->name,
            'flat' => $this->flat?->property_number,
            'complaint' => $this->complaint,
            'categoty' => $this->category,
            'opened_on' => $this->open_time_diff,
            'resolved' => $this->status == 'open' ? false : true,
            'media' => MediaResource::collection($this->media),
            'complaint_type' => $this->complaint_type,
            'complaint_details' => $this->complaint_details,
            'priority' => $priority,
            'due_date' => $this->due_date
        ];
    }
}
