<?php

namespace App\Http\Resources\GateKeeper;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatrollingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
Log::info("Patrolling Resource". json_encode($request));
        return [
            'id' => $this->id,
            'building' => $this->building->name,
            'patrolled_by' => $this->user->name,
            'is_completed' => $this->is_completed,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'total_count' => $this->total_count,
            'completed_count' => $this->completed_count,
            'pending_count' => $this->pending_count,
            'patrolling_list' => (!$this->patrollingList) ? [] : $this->patrollingList?->map(function ($item) {
                return [
                    'location_id' => $item->location_id,
                    'location_name' => $item->location_name,
                    'patrolled_at' => $item->patrolled_at,
                    'is_completed' => $item->is_completed,
                    'floor' => $item->floor ? $item->floor->floors : null
                ];
            })?->sortBy('patrolled_at')?->values()?->toArray()
        ];
    }
}
