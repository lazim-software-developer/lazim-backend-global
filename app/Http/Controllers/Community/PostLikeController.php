<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Http\Resources\User\UserResource;
use App\Models\Community\Post;
use App\Models\Community\PostLike;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;

class PostLikeController extends Controller
{
    use UtilsTrait;
    public function like(Post $post)
    {
        $existingLike = PostLike::where('post_id', $post->id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($existingLike) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You have already liked this post.',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        $postLike = PostLike::create([
            'post_id' => $post->id,
            'user_id' => auth()->user()->id
        ]);
        $expoPushTokens = ExpoPushNotification::where('user_id', $postLike->user_id)->pluck('token');
        if ($expoPushTokens->count() > 0) {
            foreach ($expoPushTokens as $expoPushToken) {
                $message = [
                    'to' => $expoPushToken,
                    'sound' => 'default',
                    'title' => $post->content. 'Post liked',
                    'body' => 'Your po',
                    'data' => ['notificationType' => 'app_notification'],
                ];
                $this->expoNotification($message);
            }
        }

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Post liked successfully.',
            'code' => 200,
        ]))->response()->setStatusCode(200);
    }

    public function unlike(Post $post)
    {
        $existingLike = PostLike::where('post_id', $post->id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (!$existingLike) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You have not liked this post.',
                'code' => 400,
            ]))->response()->setStatusCode(400);
        }

        $existingLike->delete();

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Post unliked successfully.',
            'code' => 200,
        ]))->response()->setStatusCode(200);
    }

    public function likers(Post $post)
    {
        // Fetch users who liked the post
        $users = $post->likes->map(function ($like) {
            return $like->user;
        });

        return UserResource::collection($users);
    }
}
