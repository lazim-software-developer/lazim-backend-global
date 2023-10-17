<?php

namespace App\Http\Controllers\Community;

use App\Filament\Resources\User\UserResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponseResource;
use App\Models\Community\Post;
use App\Models\Community\PostLike;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function like(Post $post, Request $request)
    {
        $existingLike = PostLike::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingLike) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You have already liked this post.',
                'errorCode' => 400,
            ]))->response()->setStatusCode(400);
        }

        PostLike::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id
        ]);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Post liked successfully.',
            'errorCode' => 200,
        ]))->response()->setStatusCode(200);
    }

    public function unlike(Post $post, Request $request)
    {
        $existingLike = PostLike::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$existingLike) {
            return (new CustomResponseResource([
                'title' => 'Error',
                'message' => 'You have not liked this post.',
                'errorCode' => 400,
            ]))->response()->setStatusCode(400);
        }

        $existingLike->delete();

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Post unliked successfully.',
            'errorCode' => 200,
        ]))->response()->setStatusCode(200);
    }

    public function likers(Post $post)
    {
        // Fetch users who liked the post
        $users = $post->likes->pluck('user');

        return UserResource::collection($users);
    }
}
