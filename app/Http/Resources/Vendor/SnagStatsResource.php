<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SnagStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total' => $this->count(),
            'low' =>$this->where('priority',3)->count(),
            'medium' => $this->where('priority',2)->count(),
            'high' =>$this->where('priority',1)->count(),
            'completed' => $this->where('status','closed')->count(),
            'pending' => $this->where('status','open')->count(),
        ];
    }
}
