<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorComplaintsResource extends JsonResource
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
            'complaint_details' => $this->complaint_details,
            'assignee' => new UserResource($this->technician),
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'service_id' => $this->service?->id,
            'service_name' => $this->service?->name,
            'building_id' => $this->building->id,
            'building_name' => $this->building->name,
        ];
    }
}
