<?php

namespace App\Http\Resources\Technician;

use App\Filament\Resources\MediaResource;
use App\Http\Resources\Community\CommentResource;
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
        
        return [
            'id' => $this->id,
            'complaint' => $this->complaint,
            'categoty' => $this->category,
            'opened_on' => $this->open_time_diff,
            'resolved' => $this->status == 'open' ? false : true,
            'media' => MediaResource::collection($this->media),
            'complaint_type' => $this->complaint_type,
            'complaint_details' => $this->complaint_details,
            // 'priority' : 
        ];
    }
}
