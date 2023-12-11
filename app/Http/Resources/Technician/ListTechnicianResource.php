<?php

namespace App\Http\Resources\Technician;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListTechnicianResource extends JsonResource
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
            'technician_name' => $user->first_name,
        ];
    }
}
