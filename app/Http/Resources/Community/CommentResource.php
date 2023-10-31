<?php

namespace App\Http\Resources\Community;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'content' => $this->body,
            'created_at' => $this->created_at_diff,
            'user' => new UserResource($this->user),
        ];
    }
}
