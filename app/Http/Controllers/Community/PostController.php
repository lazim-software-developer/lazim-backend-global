<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\CreatePostRequest;
use App\Models\Community\Post;
use App\Traits\UtilsTrait;
use App\Http\Resources\Community\PostResource;
use App\Http\Resources\CustomResponseResource;
use App\Models\Building\Building;
use App\Models\ExpoPushNotification;
use App\Models\Media;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use UtilsTrait;
    /**
     * Display a listing of the posts for a specific building.
     *
     * @param  int  $buildingId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Building $building)
    {
        $this->authorize('viewAny', [Post::class, $building->id]);

        // Start the query on the Post model
        $query = Post::where('status', 'published')
            ->where('scheduled_at', '<=', now())
            ->whereHas('building', function ($q) use ($building) {
                $q->where('buildings.id', $building->id);
            });

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

    public function store(CreatePostRequest $request, Building $building)
    {
        $this->authorize('create', [Post::class, $building->id]);
        // Create a new post with the provided data and the building_id and user_id
        $post = Post::create([
            'content' => $request->content,
            'user_id' => auth()->user()->id,
            'is_announcement' => $request->is_announcement ?? false,
            'status' => 'published',
            'scheduled_at' => now(),
            'owner_association_id' => $building->owner_association_id
        ]);

        // Attach building to post
        $post->Building()->sync($building);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = optimizeAndUpload($image, 'dev');

                // Create a new media entry for each image
                $media = new Media([
                    'name' => basename($imagePath), // Extracts filename from the full path
                    'url' => $imagePath,
                ]);

                // Attach the media to the post
                $post->media()->save($media);
            }
        }

        return (new CustomResponseResource([
            'title' => 'Success',
            'message' => 'Post created successfully!',
            'code' => 201,
        ]))->response()->setStatusCode(201);
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

    /**
     * Check if post uploading is enabled for this building
     *
     * @param  Post  $post
     * @return \Illuminate\Http\Response
     */
    public function checkPostUploadPermission(Building $building)
    {
        return response()->json(['allow_post_upload' => $building->allow_postupload]);
    }
}
