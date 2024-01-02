<?php

namespace App\Http\Resources\Notifications;

use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $jsonData = json_decode($this->data, true);

        return [
            'id' => $this->id,
            'title' => $jsonData['title'] ?? null,
            'body' => $jsonData['body'] ?? null,
            'isSubmitted' =>$this->read_at ? true : false,
            'date'=>Carbon::parse($this->created_at)->diffForHumans(),
        ];
    }
}

