<?php
namespace App\Http\Resources;

use App\Models\Building\Building;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PermitWorkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'facility_name' => $this->bookable->name,
            'facility_id'   => $this->bookable->id,
            'facility_icon' => $this->bookable->icon ? env('AWS_URL') . '/' . $this->bookable->icon : null,
            'date'          => Carbon::parse($this->date)->format('jS M Y'),
            'start_time'    => Carbon::parse($this->start_time)->format('h:ia'),
            'end_time'      => Carbon::parse($this->end_time)->format('h:ia'),
            'approved'      => (bool) $this->approved,
            'description'   => $this->description,
            'building_name'      => Building::where('id', $this->building_id)->first()->name,
            'user' => User::where('id', $this->user_id)->first()->first_name,
        ];
    }
}
