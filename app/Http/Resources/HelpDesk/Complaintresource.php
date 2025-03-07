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
        $priority = 'Low';

        if($this->priority === 1) {
            $priority = 'High';
        } else if($this->priority == 2) {
            $priority = 'Medium';
        }

        return [
            'id' => $this->id,
            'complaint' => $this->complaint,
            'building_id' => $this->building->id,
            'building' => $this->building->name,
            'flat' => $this->flat?->property_number,
            'service_id' => $this->service?->id,
            'selected_service' => $this->selected_service,
            'service' => $this->service?->name,
            'category' => $this->category,
            'remarks' => $this->remarks,
            'opened_on' => $this->open_time_diff,
            'closed_on' => $this->close_time?->toDateString(),
            'resolved' => $this->status != 'closed' ? false : true,
            'media' => MediaResource::collection($this->media),
            'complaint_type' => $this->complaint_type,
            'complaint_details' => $this->complaint_details,
            'comments' => CommentResource::collection($this->whenLoaded('comments', function () {
                return $this->comments()->latest()->get();
            })),
            'assignee_id' => $this->technician?->technicianVendors->first()?->id,
            'assignee_name' => $this->technician?->first_name,
            'priority' => $this->priority,
            'priority_name' => $priority,
            'due_date' => $this->due_date,
            'complaint_location' => $this->complaint_location,
            'status' => $this->status,
        ];
    }
}
