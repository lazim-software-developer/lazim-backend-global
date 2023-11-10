<?php

namespace App\Http\Resources\Vendor;

use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Master\Service;
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
        $service = Service::find($this->service_id);
        $building = Building::find($this->building_id);
        return [
            'id' => $this->id,
            'complaint' => $this->complaint,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'service_id' => $service?->id,
            'service_name' => $service?->name,
            'building_id' => $building->id,
            'building_name' => $building?->name
        ];
    }
}
