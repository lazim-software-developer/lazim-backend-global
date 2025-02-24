<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceStatsResource extends JsonResource
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
            'rejected' => $this->where('status', 'rejected')->count(),
            'pending' => $this->where('status', 'pending')->count(),
            'approved' => $this->where('status', 'approved')->count(),
        ];
    }
}
