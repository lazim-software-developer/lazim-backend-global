<?php

namespace App\Http\Resources\Vendor;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
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
            'owner_assoication' => $this->owner_association_id,
            'status' => $this->status,
            'owner' => new UserResource($this->user)
        ];
    }
}
