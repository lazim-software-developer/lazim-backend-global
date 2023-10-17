<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\CreatePostRequest;
use App\Models\Community\Post;
use App\Http\Resources\Community\PostResource;
use App\Models\Media;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the posts for a specific building.
     *
     * @param  int  $buildingId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $buildingId)
    {
        $this->authorize('viewAny', [Post::class, $buildingId]);

        $query = Post::where('building_id', $buildingId)
                 ->where('status', 'published')
                 ->where('scheduled_at', '<=', now());

        // If the request has a type parameter, filter by it
        if ($request->has('type')) {
            $type = $request->input('type');
            if ($type === 'announcement') {
                $query->where('is_announcement', true);
            } elseif ($type === 'post') {
                $query->where('is_announcement', false);
            }
        }

        // Paginate and get latest post first
        $posts = $query->latest()->paginate(10);
        return PostResource::collection($posts);
    }

    public function store(CreatePostRequest $request, $buildingId)
    {
        $this->authorize('create', [Post::class, $buildingId]);
        // Create a new post with the provided data and the building_id and user_id
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'building_id' => $buildingId,
            'user_id' => auth()->user()->id,
            'is_announcement' => $request->is_announcement ?? false
        ]);

        // Change this to upload images
        $media = new Media([
            'name' => 'one',
            'url' => 'path',
        ]);

        $post->media()->save($media);

        return $post;
    }

    /**
     * Display a post and details about the post.
     *
     * @param  Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $this->authorize('view', $post);

        $post->load('comments');
        return new PostResource($post);
    }
}
