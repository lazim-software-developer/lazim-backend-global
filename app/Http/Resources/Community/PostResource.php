<?php

namespace App\Http\Resources\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'user_id' => auth()->user()->id,
            'content' => $this->content,
            'media' => $this->media,
            'liked' => $this->is_liked_by_user,
            'likes' => $this->likes()->count(),
            'comments' => $this->whenLoaded('comments')
        ];
    }
}
