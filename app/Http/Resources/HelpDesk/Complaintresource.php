<?php

namespace App\Http\Resources\HelpDesk;

use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Complaintresource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'complaint' => $this->complaint,
            'categoty' => $this->category,
            'opened_on' => $this->open_time_diff,
            'resolved' => $this->status == 'open' ? false : true,
            'media' => MediaResource::collection($this->media),
            'complaint_type' => $this->complaint_type,
            'complaint_details' => $this->complaint_details
        ];
    }
}
