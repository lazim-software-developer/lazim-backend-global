<?php

namespace App\Http\Resources;

use App\Models\UserApproval;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlatTenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $flatIds = UserApproval::where('user_id', $this->tenant_id)
            ->where('status', 'approved')
            ->where('flat_id', $this->flat_id)
            ->exists();

        return [
            'id'                    => $this->id,
            'tenant_id'             => $this->tenant_id,
            'tenant_name'           => $this->user?->first_name,
            'flat_id'               => $this->flat_id,
            'flat_number'           => $this->flat?->property_number,
            'active'                => $this->active && $flatIds,
            'building_id'           => $this->building_id,
            'building_name'         => $this->building?->name,
            'start_date'            => $this->start_date,
            'end_date'              => $this->end_date,
            'role'                  => $this->role,
            'residing_in_same_flat' => $this->residing_in_same_flat,
        ];
    }
}
