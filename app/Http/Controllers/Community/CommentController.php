<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\StoreCommentRequest;
use App\Http\Resources\Community\CommentResource;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Complaint;
use App\Models\Community\Comment;
use App\Models\Community\Post;
use App\Models\ExpoPushNotification;
use App\Traits\UtilsTrait;

class CommentController extends Controller
{
    use UtilsTrait;
    // List all comments for a post in community
    public function index(Post $post)
    {
        // Fetch paginated comments for the post
        $comments = $post->comments()->paginate(10);

        return CommentResource::collection($comments);
    }

    // Add a comment for a post in community
    public function store(StoreCommentRequest $request, Post $post)
    {
        $comment = new Comment($request->all());

        $comment->commentable()->associate($post);
        $comment->user_id = auth()->user()->id;
        $comment->save();

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => "Comment added successfully",
            'code' => 201,
            'status' => 'success',
            'data' => new CommentResource($comment)
        ]))->response()->setStatusCode(201);
    }

    // Add a comment for a complaint in help-desk
    public function addComment(StoreCommentRequest $request, Complaint $complaint)
    {
        $comment = new Comment([
            'body' => $request->body,
            'user_id' => auth()->user()->id,
        ]);

        $complaint->comments()->save($comment);

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => "Comment added successfully",
            'code' => 201,
            'status' => 'success',
            'data' => new CommentResource($comment)
        ]))->response()->setStatusCode(201);
    }

    // List all comments for a given complaint
    public function listComplaintComments(Complaint $complaint)
    {
        $comments = $complaint->comments()->orderBy('id', 'desc')->get();

        return CommentResource::collection($comments);
    }
}
