<?php

namespace App\Http\Resources\HelpDesk;

use App\Http\Resources\Community\CommentResource;
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
            'id' => $this->id,
            'complaint' => $this->complaint,
            'building' => $this->building->name,
            'flat' => $this->flat?->property_number,
            'service' => $this->service?->name,
            'category' => $this->category,
            'opened_on' => $this->open_time_diff,
            'resolved' => $this->status == 'open' ? false : true,
            'media' => MediaResource::collection($this->media),
            'complaint_type' => $this->complaint_type,
            'complaint_details' => $this->complaint_details,
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
