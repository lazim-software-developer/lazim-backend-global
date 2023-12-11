<?php

namespace App\Http\Resources\Community;

use App\Http\Resources\User\UserResource;
use App\Http\Resources\MediaResource;
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
            'user' => new UserResource($this->user),
            'content' => $this->content,
            'media' => MediaResource::collection($this->media),
            'liked' => $this->is_liked_by_user,
            'likes' => $this->likes()->count(),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'date' => $this->scheduled_at_diff,
            'allow_comment' =>$this->allow_comment,
            'allow_like' => $this->allow_like
        ];
    }
}
