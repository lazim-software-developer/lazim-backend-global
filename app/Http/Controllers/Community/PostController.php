<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\Post;
use App\Http\Resources\Community\PostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $query = Post::where('building_id', $buildingId);

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
        $posts = $query->latest()->paginate();

        return new PostResource($posts);
    }
}
