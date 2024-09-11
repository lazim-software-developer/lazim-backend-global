<?php

namespace App\Http\Resources;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListAllTechnicianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->technician_id);
        return [
            'id' => $this->id,
            "technician_id" => $this->technician_id,
            'technician_name' => $user->first_name,
            "technician_number" => $this->technician_number,
            "technician_email" => $user->email,
            "technician_phone" => $user->phone,
            "technician_position" => $this->position,
            "active" => $user->active,
        ];
    }
}
