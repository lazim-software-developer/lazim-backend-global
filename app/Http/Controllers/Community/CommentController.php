<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommentRequest;
use App\Http\Resources\CustomResponseResource;
use App\Models\Community\Comment;
use App\Models\Community\Post;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post)
    {
        $data = $request->validated();

        $comment = new Comment($request->all());
        $comment->commentable()->associate($post);
        $comment->user_id = auth()->id();
        $comment->save();

        return new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Comment added successfully',
            'data' => $comment,
            'errorCode' => 201,
        ]);
    }
}
